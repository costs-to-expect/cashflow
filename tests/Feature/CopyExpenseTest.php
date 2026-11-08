<?php

namespace Tests\Feature;

use App\Service\Api\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http as HttpFacade;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * The copy form: the add-expense form prefilled from an existing expense.
 * It is the edit form's fetch (see PooledFormPagesTest) rendered as the add form.
 */
class CopyExpenseTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    private const RT = 'api.test/v3/resource-types/rt-1';

    private const PATH = '/v3/resource-types/rt-1';

    private const COPY = '/resource-types/rt-1/resources/r-1/expenses/i-1/copy';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    /**
     * The fake API's i-1, with anything given changed.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function itemOverride(array $extra = []): array
    {
        return [self::RT.'/resources/r-1/items/i-1' => HttpFacade::response([
            'id' => 'i-1',
            'name' => 'Rent for Ada',
            'description' => 'Monthly',
            'effective_date' => '2026-09-01',
            'currency' => ['id' => 'gbp', 'code' => 'GBP'],
            'total' => '50.00',
            'percentage' => 100,
            ...$extra,
        ], 200)];
    }

    /**
     * The first row's resource <select> of the split card, so the always
     * present row <template> (which starts on the first resource) can't
     * pass for the row the form chose.
     */
    private function firstAllocationSelect(string $html): string
    {
        preg_match('/<select name="allocations\[0\]\[resource_id\]".*?<\/select>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    private function firstAllocationPercentage(string $html): ?string
    {
        preg_match('/name="allocations\[0\]\[percentage\]" value="([^"]*)"/', $html, $matches);

        return $matches[1] ?? null;
    }

    // ---- Fetching

    public function test_the_copy_form_fetches_the_same_way_the_edit_form_does(): void
    {
        $this->signedIn()->get(self::COPY)->assertOk();

        $this->assertSame([null, ...array_fill(0, 6, 1), ...array_fill(0, 5, 2)], array_column(Http::requests(), 'pool'));

        $this->assertSame([
            self::PATH.'/resources/r-1/items/i-1',
            self::PATH.'/resources/r-1/items/i-1/categories',
        ], array_values(array_intersect($this->uris(1), [
            self::PATH.'/resources/r-1/items/i-1',
            self::PATH.'/resources/r-1/items/i-1/categories',
        ])));

        // The expense's subcategory assignment needs its category assignment's id.
        $this->assertContains(self::PATH.'/resources/r-1/items/i-1/categories/ic-1/subcategories', $this->uris(2));
    }

    public function test_the_copy_form_skips_the_subcategory_request_for_an_uncategorised_expense(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1/categories' => HttpFacade::response([], 200)]);

        $this->signedIn()->get(self::COPY)->assertOk();

        $this->assertSame([6, 4], array_column(Http::pools(), 'size'));
        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_ends_with($uri, '/ic-1/subcategories')));
    }

    public function test_the_copy_form_does_not_fetch_categories_while_they_are_off(): void
    {
        $this->resourceType->setCategoriesEnabled(false);

        $this->signedIn()->get(self::COPY)
            ->assertOk()
            ->assertDontSee('name="category_id"', false);

        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_contains($uri, 'categories')));
    }

    public function test_the_copy_form_is_a_404_for_an_expense_that_does_not_exist_without_a_second_pool(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1' => HttpFacade::response(['message' => 'Not found'], 404)]);

        $this->signedIn()->get(self::COPY)->assertNotFound();

        $this->assertSame([6], array_column(Http::pools(), 'size'));
    }

    // ---- What it is prefilled with

    public function test_the_copy_form_is_the_add_form_prefilled_with_the_expense(): void
    {
        $this->signedIn()->get(self::COPY)
            ->assertOk()
            ->assertSee('Copy expense')
            ->assertSee('Add copy')
            // It adds a new expense: the same route as the add form.
            ->assertSee('action="'.url('/resource-types/rt-1/expenses').'"', false)
            ->assertSee('value="Rent for Ada"', false)
            ->assertSee('Monthly')
            ->assertSee('value="50.00"', false)
            ->assertSee('value="2026-09-01"', false)
            ->assertSee('<option value="gbp" selected>GBP</option>', false)
            ->assertDontSee('<option value="usd" selected>', false)
            // Its category and subcategory, the category's subcategories being what the select starts with.
            ->assertSee('<option value="c-2" selected>School</option>', false)
            ->assertSee('data-initial="s-2"', false)
            ->assertSee('>Trips</option>', false)
            ->assertDontSee('>Lunch</option>', false);
    }

    public function test_cancel_and_back_go_to_the_resource_the_expense_is_on(): void
    {
        $this->signedIn()->get(self::COPY)
            ->assertOk()
            ->assertSee('href="'.url('/resource-types/rt-1/resources/r-1').'"', false)
            ->assertDontSee('Dashboard');
    }

    public function test_the_copy_keeps_the_expenses_resource_and_percentage(): void
    {
        $this->fakeApi($this->itemOverride(['percentage' => 40]));

        $html = $this->signedIn()->get(self::COPY)->assertOk()->getContent();

        $this->assertStringContainsString('<option value="r-1" selected>Ada</option>', $this->firstAllocationSelect($html));
        $this->assertSame('40', $this->firstAllocationPercentage($html));
    }

    public function test_the_copy_keeps_the_publish_after_date(): void
    {
        $this->fakeApi($this->itemOverride(['publish_after' => '2026-11-01']));

        $this->signedIn()->get(self::COPY)->assertOk()->assertSee('value="2026-11-01"', false);
    }

    public function test_the_copy_form_leaves_publish_after_empty_when_the_api_does_not_say(): void
    {
        $html = $this->signedIn()->get(self::COPY)->assertOk()->getContent();

        // The input is there, with no date in it.
        $this->assertMatchesRegularExpression('/name="publish_after"\s+type="date"\s+value=""/', $html);
    }

    // ---- Copying to another resource

    public function test_to_selects_the_resource_the_copy_goes_to(): void
    {
        $html = $this->signedIn()->get(self::COPY.'?to=r-2')->assertOk()->getContent();

        $select = $this->firstAllocationSelect($html);

        $this->assertStringContainsString('<option value="r-2" selected>Ben</option>', $select);
        $this->assertStringNotContainsString('<option value="r-1" selected>', $select);
        // Only the resource changes.
        $this->assertSame('100', $this->firstAllocationPercentage($html));
    }

    public function test_to_keeps_the_percentage_of_the_expense(): void
    {
        $this->fakeApi($this->itemOverride(['percentage' => 40]));

        $html = $this->signedIn()->get(self::COPY.'?to=r-2')->assertOk()->getContent();

        $this->assertSame('40', $this->firstAllocationPercentage($html));
    }

    public function test_a_to_that_is_not_one_of_the_resources_is_ignored(): void
    {
        foreach (['?to=nope', '?to[]=r-2', '?to='] as $query) {
            $html = $this->signedIn()->get(self::COPY.$query)->assertOk()->getContent();

            $this->assertStringContainsString('<option value="r-1" selected>Ada</option>', $this->firstAllocationSelect($html), $query);
        }
    }

    // ---- Copy to, from the pop-up

    private const COPY_TO = '/resource-types/rt-1/resources/r-1/expenses/i-1/copy-to';

    /**
     * What the API answers when the copy is created on Ben (r-2) as i-9 and
     * given its category and subcategory.
     *
     * @return array<string, mixed>
     */
    private function copyCreated(): array
    {
        return [
            self::RT.'/resources/r-2/items' => HttpFacade::response(['id' => 'i-9'], 201),
            self::RT.'/resources/r-2/items/i-9/categories' => HttpFacade::response(['id' => 'ic-9'], 201),
            self::RT.'/resources/r-2/items/i-9/categories/ic-9/subcategories' => HttpFacade::response(['id' => 'is-9'], 201),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sentBodies(string $method, string $urlEnd): array
    {
        $bodies = [];

        foreach (HttpFacade::recorded() as [$request]) {
            if ($request->method() === $method && str_ends_with($request->url(), $urlEnd)) {
                $bodies[] = $request->data();
            }
        }

        return $bodies;
    }

    /**
     * Every request the API was sent that wasn't a read.
     *
     * @return list<string>
     */
    private function writes(): array
    {
        $writes = [];

        foreach (HttpFacade::recorded() as [$request]) {
            if ($request->method() !== 'GET') {
                $writes[] = $request->method().' '.$request->url();
            }
        }

        return $writes;
    }

    public function test_copy_to_creates_the_expense_as_it_is_on_the_other_resource(): void
    {
        $this->fakeApi($this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])
            ->assertRedirect(url('/resource-types/rt-1/resources/r-2'))
            ->assertSessionHas('status', 'Rent for Ada has been copied to Ben.');

        $this->assertSame([[
            'name' => 'Rent for Ada',
            'description' => 'Monthly',
            'effective_date' => '2026-09-01',
            'currency_id' => 'gbp',
            'total' => '50.00',
        ]], $this->sentBodies('POST', '/resources/r-2/items'));

        // Copied, not moved.
        $this->assertSame([], $this->sentBodies('POST', '/resources/r-1/items'));
        $this->assertSame([], $this->sentBodies('DELETE', '/resources/r-1/items/i-1'));
    }

    public function test_copy_to_gives_the_copy_the_category_and_subcategory(): void
    {
        $this->fakeApi($this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])->assertRedirect();

        $this->assertSame([['category_id' => 'c-2']], $this->sentBodies('POST', '/resources/r-2/items/i-9/categories'));
        $this->assertSame([['subcategory_id' => 's-2']], $this->sentBodies('POST', '/resources/r-2/items/i-9/categories/ic-9/subcategories'));
    }

    public function test_copy_to_keeps_the_percentage_and_publish_after_date(): void
    {
        $this->fakeApi($this->itemOverride(['percentage' => 40, 'publish_after' => '2026-11-01']) + $this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])->assertRedirect();

        $body = $this->sentBodies('POST', '/resources/r-2/items')[0];

        $this->assertSame(40, $body['percentage']);
        $this->assertSame('2026-11-01', $body['publish_after']);
    }

    public function test_copy_to_reads_the_expense_the_resources_and_the_category_together_then_the_subcategory(): void
    {
        $this->fakeApi($this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])->assertRedirect();

        $this->assertSame([3], array_column(Http::pools(), 'size'));

        $pooled = $this->uris(1);
        sort($pooled);

        $this->assertSame([
            self::PATH.'/resources/r-1/items/i-1',
            self::PATH.'/resources/r-1/items/i-1/categories',
            self::PATH.'/resources?collection=true',
        ], $pooled);

        // It needs the category assignment's id, and is one request so not a pool. The sign-in check comes first.
        $this->assertContains(self::PATH.'/resources/r-1/items/i-1/categories/ic-1/subcategories', $this->uris());
    }

    public function test_copy_to_copies_an_uncategorised_expense_without_a_category(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1/categories' => HttpFacade::response([], 200)] + $this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])
            ->assertRedirect(url('/resource-types/rt-1/resources/r-2'))
            ->assertSessionHas('status');

        $this->assertSame(['POST '.'http://api.test'.self::PATH.'/resources/r-2/items'], $this->writes());
        $this->assertSame([3], array_column(Http::pools(), 'size'));
    }

    public function test_copy_to_makes_no_category_calls_while_categories_are_off(): void
    {
        $this->resourceType->setCategoriesEnabled(false);
        $this->fakeApi($this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])->assertRedirect()->assertSessionHas('status');

        $this->assertSame([], array_filter($this->uris(), fn ($uri) => str_contains($uri, 'categories')));
        $this->assertSame(['POST '.'http://api.test'.self::PATH.'/resources/r-2/items'], $this->writes());
    }

    public function test_copy_to_refuses_the_expenses_own_resource_and_ones_that_do_not_exist(): void
    {
        $this->fakeApi($this->copyCreated());

        foreach (['r-1', 'nope'] as $target) {
            $this->signedIn()->from('/resource-types/rt-1/resources/r-1')
                ->post(self::COPY_TO, ['target_resource_id' => $target])
                ->assertRedirect('/resource-types/rt-1/resources/r-1')
                ->assertSessionHas('danger');
        }

        $this->assertSame([], $this->writes());
    }

    public function test_copy_to_needs_a_target(): void
    {
        $this->fakeApi($this->copyCreated());

        $this->signedIn()->post(self::COPY_TO)->assertSessionHasErrors('target_resource_id');

        $this->assertSame([], $this->writes());
    }

    public function test_copy_to_an_expense_that_does_not_exist_creates_nothing(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items/i-1' => HttpFacade::response(['message' => 'Not found'], 404)] + $this->copyCreated());

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])->assertSessionHas('danger');

        $this->assertSame([], $this->writes());
        $this->assertSame([3], array_column(Http::pools(), 'size'));
    }

    public function test_copy_to_tells_you_when_the_api_refuses_the_copy_rather_than_losing_the_errors(): void
    {
        $this->fakeApi([
            self::RT.'/resources/r-2/items' => HttpFacade::response(['fields' => ['total' => ['errors' => ['Too big']]]], 422),
        ]);

        $this->signedIn()->post(self::COPY_TO, ['target_resource_id' => 'r-2'])
            ->assertSessionHas('danger')
            ->assertSessionHasNoErrors();
    }

    public function test_the_list_offers_copy_to_with_the_other_resources_only(): void
    {
        $html = $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            ->assertSee('data-copy-to', false)
            ->assertSee('data-copy-url="'.url(self::COPY_TO).'"', false)
            ->assertSee('data-adjust-url="'.url(self::COPY).'"', false)
            ->assertSee('/copy-to.js', false)
            ->getContent();

        preg_match('/<dialog id="copy-to-dialog".*?<\/dialog>/s', $html, $matches);

        $this->assertNotEmpty($matches);
        $this->assertStringContainsString('<option value="r-2" selected>Ben</option>', $matches[0]);
        $this->assertStringNotContainsString('>Ada</option>', $matches[0]);
    }

    public function test_the_list_does_not_offer_copy_to_with_only_one_resource(): void
    {
        $this->fakeApi([self::RT.'/resources?collection=true' => HttpFacade::response([['id' => 'r-1', 'name' => 'Ada', 'description' => null]], 200)]);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            // Copy still is.
            ->assertSee('href="'.url(self::COPY).'"', false)
            ->assertDontSee('data-copy-to', false)
            ->assertDontSee('copy-to-dialog', false)
            ->assertDontSee('/copy-to.js', false);
    }

    // ---- The rest of the app

    public function test_the_add_form_is_unchanged(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/expenses/create')
            ->assertOk()
            ->assertSee('Add expense')
            ->assertDontSee('Copy expense')
            ->assertDontSee('Add copy')
            ->assertSee('Dashboard');
    }

    public function test_each_expense_in_a_resources_list_has_a_copy_link(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            ->assertSee('href="'.url(self::COPY).'"', false);
    }
}
