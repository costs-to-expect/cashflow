<?php

namespace Tests\Feature;

use App\Models\RecurringExpense;
use App\Models\ResourceType;
use App\Service\Api\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http as HttpFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * The form and settings pages whose API calls are pooled, see
 * PooledPagesTest. The forms need two waves: the lists (resources,
 * currencies, categories), then what needs them (each category's
 * subcategories, each resource's recent names, the expense's own
 * subcategory assignment).
 */
class PooledFormPagesTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    private const RT = '/v3/resource-types/rt-1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    private function names(): string
    {
        return '?limit=100&sort=effective_date%3Adesc';
    }

    /**
     * Sorted, to compare which requests went in a pool without depending on
     * the order they were added in.
     *
     * @param  list<string>  $uris
     * @return list<string>
     */
    private function sorted(array $uris): array
    {
        sort($uris);

        return $uris;
    }

    private function recurringExpense(?ResourceType $resourceType = null): RecurringExpense
    {
        $recurring = RecurringExpense::create([
            'resource_type_id' => ($resourceType ?? $this->resourceType)->id,
            'name' => 'Swimming lessons',
            'description' => null,
            'currency_id' => 'gbp',
            'total' => 40,
            'category_id' => 'c-2',
            'subcategory_id' => 's-2',
            'frequency' => 'monthly',
            'day_of_month' => 1,
            'starts_on' => '2026-01-01',
            'next_run_date' => '2026-10-01',
            'active' => true,
        ]);

        $recurring->allocations()->create(['resource_id' => 'r-1', 'percentage' => 100, 'sort_order' => 0]);

        return $recurring;
    }

    /**
     * The subcategories the form's category select is given, as the script
     * that keeps it and the subcategory select in step reads them.
     *
     * @return array<string, list<array{id: string, name: string}>>
     */
    private function subcategoriesIn(string $html): array
    {
        preg_match('/data-subcategories="([^"]*)"/', $html, $matches);

        return json_decode(html_entity_decode($matches[1]), true);
    }

    // ---- Add expense

    public function test_the_add_expense_form_fetches_in_two_pools_after_the_sign_in_check(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/expenses/create')->assertOk();

        $this->assertSame([null, ...array_fill(0, 4, 1), ...array_fill(0, 4, 2)], array_column(Http::requests(), 'pool'));

        // Nothing here needs another response: the resources, the nav's
        // permitted resource types, currencies and categories.
        $this->assertSame($this->sorted([
            self::RT.'/resources?collection=true',
            '/v3/auth/user/permitted-resource-types',
            '/v3/currencies?collection=true',
            self::RT.'/categories?collection=true',
        ]), $this->sorted($this->uris(1)));

        // These need the ids in them.
        $this->assertSame($this->sorted([
            self::RT.'/categories/c-1/subcategories?collection=true',
            self::RT.'/categories/c-2/subcategories?collection=true',
            self::RT.'/resources/r-1/items'.$this->names(),
            self::RT.'/resources/r-2/items'.$this->names(),
        ]), $this->sorted($this->uris(2)));
    }

    public function test_the_add_expense_form_puts_each_response_where_it_belongs(): void
    {
        $response = $this->signedIn()->get('/resource-types/rt-1/expenses/create')
            ->assertOk()
            // GBP first whatever order the API returned them in.
            ->assertSeeInOrder(['>GBP</option>', '>USD</option>'], false)
            // Each resource's recent expense names, for the suggestions.
            ->assertSee('<option value="Rent for Ada">', false)
            ->assertSee('<option value="Rent for Ben">', false);

        $this->assertSame([
            'c-1' => [['id' => 's-1', 'name' => 'Lunch']],
            'c-2' => [['id' => 's-2', 'name' => 'Trips']],
        ], $this->subcategoriesIn($response->getContent()));
    }

    public function test_the_add_expense_form_does_not_fetch_categories_while_they_are_off(): void
    {
        $this->resourceType->setCategoriesEnabled(false);

        $this->signedIn()->get('/resource-types/rt-1/expenses/create')->assertOk();

        $this->assertSame([null, ...array_fill(0, 3, 1), ...array_fill(0, 2, 2)], array_column(Http::requests(), 'pool'));
        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_contains($uri, 'categories')));
    }

    // ---- Edit expense

    public function test_the_edit_expense_form_fetches_in_two_pools_after_the_sign_in_check(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')->assertOk();

        $this->assertSame([null, ...array_fill(0, 6, 1), ...array_fill(0, 5, 2)], array_column(Http::requests(), 'pool'));

        $this->assertSame($this->sorted([
            self::RT.'/resources/r-1/items/i-1',
            self::RT.'/resources/r-1/items/i-1/categories',
            self::RT.'/resources?collection=true',
            '/v3/auth/user/permitted-resource-types',
            '/v3/currencies?collection=true',
            self::RT.'/categories?collection=true',
        ]), $this->sorted($this->uris(1)));

        // The expense's subcategory assignment needs its category
        // assignment's id, so it waits for the first pool.
        $this->assertSame($this->sorted([
            self::RT.'/resources/r-1/items/i-1/categories/ic-1/subcategories',
            self::RT.'/categories/c-1/subcategories?collection=true',
            self::RT.'/categories/c-2/subcategories?collection=true',
            self::RT.'/resources/r-1/items'.$this->names(),
            self::RT.'/resources/r-2/items'.$this->names(),
        ]), $this->sorted($this->uris(2)));
    }

    public function test_the_edit_expense_form_preselects_the_expenses_own_category_and_subcategory(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')
            ->assertOk()
            ->assertSee('value="Rent for Ada"', false)
            ->assertSee('Monthly')
            // The subcategory the expense is assigned, from the second pool.
            ->assertSee('data-initial="s-2"', false)
            // Its category's subcategories are the ones the select starts with.
            ->assertSee('>Trips</option>', false)
            ->assertDontSee('>Lunch</option>', false);
    }

    public function test_the_edit_expense_form_skips_the_subcategory_request_for_an_uncategorised_expense(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1/categories' => HttpFacade::response([], 200)]);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')
            ->assertOk()
            ->assertSee('data-initial=""', false);

        $this->assertSame([6, 4], array_column(Http::pools(), 'size'));
        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_ends_with($uri, '/ic-1/subcategories')));
    }

    public function test_the_edit_expense_form_is_a_404_for_an_expense_that_does_not_exist_without_a_second_pool(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1' => HttpFacade::response(['message' => 'Not found'], 404)]);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')->assertNotFound();

        $this->assertSame([6], array_column(Http::pools(), 'size'));
    }

    public function test_the_edit_expense_form_does_not_fetch_categories_while_they_are_off(): void
    {
        $this->resourceType->setCategoriesEnabled(false);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')->assertOk();

        // The expense, resources, permitted resource types and currencies, then each resource's names.
        $this->assertSame([null, ...array_fill(0, 4, 1), ...array_fill(0, 2, 2)], array_column(Http::requests(), 'pool'));
        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_contains($uri, 'categories')));
    }

    // ---- Recurring expenses

    public function test_the_recurring_index_fetches_everything_in_one_pool(): void
    {
        $this->recurringExpense();

        $this->signedIn()->get('/resource-types/rt-1/recurring')
            ->assertOk()
            ->assertSee('Swimming lessons')
            ->assertSee('Ada (100%)')
            ->assertSee('GBP 40.00');

        $this->assertSame([null, 1, 1, 1], array_column(Http::requests(), 'pool'));
    }

    public function test_the_add_recurring_form_fetches_in_two_pools_after_the_sign_in_check(): void
    {
        $response = $this->signedIn()->get('/resource-types/rt-1/recurring/create')
            ->assertOk()
            ->assertSeeInOrder(['>GBP</option>', '>USD</option>'], false);

        $this->assertSame([null, ...array_fill(0, 4, 1), ...array_fill(0, 2, 2)], array_column(Http::requests(), 'pool'));
        $this->assertSame(['c-1', 'c-2'], array_keys($this->subcategoriesIn($response->getContent())));
    }

    public function test_the_edit_recurring_form_fetches_in_two_pools_after_the_sign_in_check(): void
    {
        $recurring = $this->recurringExpense();

        $this->signedIn()->get("/resource-types/rt-1/recurring/{$recurring->id}/edit")
            ->assertOk()
            ->assertSee('value="Swimming lessons"', false);

        $this->assertSame([null, ...array_fill(0, 4, 1), ...array_fill(0, 2, 2)], array_column(Http::requests(), 'pool'));
    }

    public function test_the_edit_recurring_form_is_a_404_for_another_resource_types_expense_without_asking_the_api(): void
    {
        $other = ResourceType::create([
            'user_id' => 'u-1',
            'name' => 'Pets',
            'description' => 'Pets',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => 'rt-2',
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);

        $recurring = $this->recurringExpense($other);

        $this->signedIn()->get("/resource-types/rt-1/recurring/{$recurring->id}/edit")->assertNotFound();

        $this->assertSame([], Http::pools());
    }

    // ---- Settings

    public function test_the_categories_settings_fetch_in_two_pools_after_the_sign_in_check(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/settings/categories')
            ->assertOk()
            // Each category with its own subcategories, not another's.
            ->assertSeeInOrder(['Food', 'Lunch', 'School', 'Trips'])
            ->assertSee('1 subcategory');

        // The categories and the nav's resources and permitted resource types, then each category's subcategories.
        $this->assertSame([null, ...array_fill(0, 3, 1), ...array_fill(0, 2, 2)], array_column(Http::requests(), 'pool'));
    }

    public function test_the_default_split_settings_fetch_the_resources_and_the_nav_together(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/settings/default-split')
            ->assertOk()
            ->assertSee('Ada')
            ->assertSee('Ben');

        $this->assertSame([null, 1, 1], array_column(Http::requests(), 'pool'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function pagesWithNoCallsOfTheirOwn(): array
    {
        return [
            'settings' => ['settings'],
            'use categories' => ['settings/use-categories'],
            'resource naming' => ['settings/resource-naming'],
            'reporting periods' => ['settings/periods'],
            'add resource' => ['resources/create'],
        ];
    }

    #[DataProvider('pagesWithNoCallsOfTheirOwn')]
    public function test_pages_that_make_no_calls_of_their_own_pool_the_nav(string $page): void
    {
        $this->signedIn()->get("/resource-types/rt-1/{$page}")->assertOk();

        $this->assertSame([null, 1, 1], array_column(Http::requests(), 'pool'));
    }
}
