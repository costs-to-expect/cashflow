<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as HttpFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * The add-expense form's always-visible percentage, and the optional "publish after"
 * date on the add and edit forms (and its badge on the resource page's list).
 */
class ExpensePercentageAndPublishAfterTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    private const RT = 'api.test/v3/resource-types/rt-1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();

        // Keeps the category assignment calls (and fields) out of these tests.
        $this->resourceType->setCategoriesEnabled(false);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function expense(array $extra = []): array
    {
        return [
            'name' => 'School trip',
            'description' => 'Deposit',
            'effective_date' => '2026-09-20',
            'currency_id' => 'gbp',
            'total' => '100.00',
            'allocations' => [['resource_id' => 'r-1', 'percentage' => 100]],
            ...$extra,
        ];
    }

    /**
     * The opening tag of the first percentage field's <label>, where the
     * script's data-split-only marker would be.
     */
    private function firstPercentageLabel(string $html): string
    {
        preg_match('/<label[^>]*>(?=\s*<span class="sr-only">Percentage<\/span>)/', $html, $matches);

        return $matches[0] ?? '';
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

    private function fakeItemCreated(): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items' => HttpFacade::response(['id' => 'i-9'], 201)]);
    }

    // ---- Percentage

    public function test_the_add_expense_form_shows_the_percentage_without_splitting(): void
    {
        $html = $this->signedIn()->get('/resource-types/rt-1/expenses/create')
            ->assertOk()
            ->assertSee('data-percentage-always="true"', false)
            ->assertSee('id="split-single-summary"', false)
            ->getContent();

        $this->assertNotSame('', $this->firstPercentageLabel($html));
        $this->assertStringNotContainsString('data-split-only', $this->firstPercentageLabel($html));
        $this->assertStringNotContainsString('hidden', $this->firstPercentageLabel($html));
    }

    public function test_the_add_expense_form_shows_the_percentage_with_only_one_resource_too(): void
    {
        $this->fakeApi([self::RT.'/resources?collection=true' => HttpFacade::response([['id' => 'r-1', 'name' => 'Ada', 'description' => null]], 200)]);

        $html = $this->signedIn()->get('/resource-types/rt-1/expenses/create')->assertOk()
            // No toggle to split with, but the percentage is there.
            ->assertDontSee('id="split-toggle"', false)
            ->getContent();

        $this->assertStringNotContainsString('data-split-only', $this->firstPercentageLabel($html));
    }

    public function test_the_recurring_form_still_hides_the_percentage_until_split(): void
    {
        $html = $this->signedIn()->get('/resource-types/rt-1/recurring/create')
            ->assertOk()
            ->assertSee('data-percentage-always="false"', false)
            ->assertDontSee('id="split-single-summary"', false)
            ->getContent();

        $this->assertStringContainsString('data-split-only', $this->firstPercentageLabel($html));
    }

    public function test_a_single_allocation_below_100_percent_is_sent_to_the_api(): void
    {
        $this->fakeItemCreated();

        $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense([
            'allocations' => [['resource_id' => 'r-1', 'percentage' => 40]],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([[
            'name' => 'School trip',
            'description' => 'Deposit',
            'effective_date' => '2026-09-20',
            'currency_id' => 'gbp',
            'total' => '100.00',
            'percentage' => 40,
        ]], $this->sentBodies('POST', '/resources/r-1/items'));
    }

    public function test_a_percentage_outside_1_to_100_is_refused(): void
    {
        $this->fakeItemCreated();

        foreach ([0, 101] as $percentage) {
            $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense([
                'allocations' => [['resource_id' => 'r-1', 'percentage' => $percentage]],
            ]))->assertSessionHasErrors('allocations.0.percentage');
        }

        $this->assertSame([], $this->sentBodies('POST', '/resources/r-1/items'));
    }

    // ---- Publish after, add

    public function test_the_add_expense_form_has_an_empty_optional_publish_after_date(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/expenses/create')
            ->assertOk()
            ->assertSee('Publish after')
            ->assertSee('name="publish_after"', false)
            ->assertDontSee('name="publish_after" type="date" required', false);
    }

    public function test_publish_after_is_sent_to_the_api_when_given(): void
    {
        $this->fakeItemCreated();

        $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense(['publish_after' => '2026-11-01']))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('2026-11-01', $this->sentBodies('POST', '/resources/r-1/items')[0]['publish_after']);
    }

    public function test_publish_after_is_left_out_when_blank(): void
    {
        $this->fakeItemCreated();

        $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense(['publish_after' => '']))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertArrayNotHasKey('publish_after', $this->sentBodies('POST', '/resources/r-1/items')[0]);
    }

    public function test_every_item_of_a_split_gets_the_same_publish_after(): void
    {
        $this->fakeApi([
            self::RT.'/resources/r-1/items' => HttpFacade::response(['id' => 'i-9'], 201),
            self::RT.'/resources/r-2/items' => HttpFacade::response(['id' => 'i-10'], 201),
        ]);

        $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense([
            'publish_after' => '2026-11-01',
            'allocations' => [
                ['resource_id' => 'r-1', 'percentage' => 60],
                ['resource_id' => 'r-2', 'percentage' => 40],
            ],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('2026-11-01', $this->sentBodies('POST', '/resources/r-1/items')[0]['publish_after']);
        $this->assertSame('2026-11-01', $this->sentBodies('POST', '/resources/r-2/items')[0]['publish_after']);
    }

    public function test_an_unusable_publish_after_is_refused_without_calling_the_api(): void
    {
        $this->fakeItemCreated();

        $this->signedIn()->post('/resource-types/rt-1/expenses', $this->expense(['publish_after' => '01/11/2026']))
            ->assertSessionHasErrors('publish_after');

        $this->assertSame([], $this->sentBodies('POST', '/resources/r-1/items'));
    }

    // ---- Publish after, edit

    /**
     * The item the edit form loads, with whatever extra keys the API returns, and an
     * accepted PATCH.
     *
     * @param  array<string, mixed>  $extra
     */
    private function fakeItem(array $extra = []): void
    {
        $this->fakeApi([
            self::RT.'/resources/r-1/items/i-1' => fn (Request $request) => $request->method() === 'PATCH'
                ? HttpFacade::response('', 204)
                : HttpFacade::response([
                    'id' => 'i-1',
                    'name' => 'Rent for Ada',
                    'description' => 'Monthly',
                    'effective_date' => '2026-09-01',
                    'currency' => ['id' => 'gbp', 'code' => 'GBP'],
                    'total' => '50.00',
                    'percentage' => 100,
                    ...$extra,
                ], 200),
        ]);
    }

    public function test_the_edit_form_shows_the_publish_after_date_the_api_reports_and_opens_the_card(): void
    {
        $this->fakeItem(['publish_after' => '2026-11-01']);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')
            ->assertOk()
            ->assertSee('value="2026-11-01"', false)
            ->assertSee('<details', false)
            ->assertSee(' open>', false);
    }

    public function test_the_edit_form_has_a_closed_empty_card_when_the_api_reports_no_date(): void
    {
        $this->fakeItem(['publish_after' => null]);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')
            ->assertOk()
            ->assertSee('name="publish_after"', false)
            ->assertDontSee('value="2026-11-01"', false)
            ->assertDontSee(' open>', false);
    }

    public function test_the_edit_form_has_no_publish_after_field_when_the_api_does_not_report_it(): void
    {
        // Saving an empty field would clear a date the form never showed.
        $this->fakeItem();

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1/expenses/i-1/edit')
            ->assertOk()
            ->assertDontSee('name="publish_after"', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function update(array $extra = []): array
    {
        return [
            'name' => 'Rent for Ada',
            'description' => 'Monthly',
            'effective_date' => '2026-09-01',
            'currency_id' => 'gbp',
            'total' => '50.00',
            'percentage' => 100,
            ...$extra,
        ];
    }

    public function test_updating_sends_a_new_publish_after_date(): void
    {
        $this->fakeItem();

        $this->signedIn()->post('/resource-types/rt-1/resources/r-1/expenses/i-1/update', $this->update(['publish_after' => '2026-12-01']))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('2026-12-01', $this->sentBodies('PATCH', '/resources/r-1/items/i-1')[0]['publish_after']);
    }

    public function test_updating_with_a_blank_publish_after_clears_it_with_an_explicit_null(): void
    {
        $this->fakeItem();

        $this->signedIn()->post('/resource-types/rt-1/resources/r-1/expenses/i-1/update', $this->update(['publish_after' => '']))
            ->assertRedirect()->assertSessionHasNoErrors();

        $body = $this->sentBodies('PATCH', '/resources/r-1/items/i-1')[0];

        $this->assertArrayHasKey('publish_after', $body);
        $this->assertNull($body['publish_after']);
    }

    public function test_updating_without_the_field_leaves_publish_after_alone(): void
    {
        $this->fakeItem();

        $this->signedIn()->post('/resource-types/rt-1/resources/r-1/expenses/i-1/update', $this->update())
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertArrayNotHasKey('publish_after', $this->sentBodies('PATCH', '/resources/r-1/items/i-1')[0]);
    }

    // ---- The list

    /**
     * @param  array<string, mixed>  $extra
     */
    private function fakeList(array $extra): void
    {
        $this->fakeApi([
            self::RT.'/resources/r-1/items*' => HttpFacade::response([[
                'id' => 'i-1',
                'name' => 'School trip',
                'description' => null,
                'effective_date' => '2026-09-01',
                'currency' => ['code' => 'GBP'],
                'actualised_total' => '40.00',
                'total' => '100.00',
                'percentage' => 40,
                ...$extra,
            ]], 200),
        ]);
    }

    public function test_the_resource_page_asks_for_unpublished_items_too(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')->assertOk();

        HttpFacade::assertSent(fn (Request $request) => str_contains($request->url(), '/resources/r-1/items?')
            && str_contains($request->url(), 'include-unpublished=true'));

        // Only the list: the totals are the published items' and are left alone.
        HttpFacade::assertNotSent(fn (Request $request) => str_contains($request->url(), '/summary/')
            && str_contains($request->url(), 'include-unpublished'));
    }

    public function test_an_item_held_back_by_publish_after_is_badged_in_the_list(): void
    {
        $this->fakeList(['publish_after' => '2026-11-01']);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            ->assertSee('School trip')
            ->assertSee('Publishes 1 Nov');
    }

    /**
     * The test clock is 2026-09-30.
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function itemsThatAreAlreadyPublished(): array
    {
        return [
            'date in the past' => [['publish_after' => '2026-09-15']],
            'date is today' => [['publish_after' => '2026-09-30']],
            'no date' => [['publish_after' => null]],
            'API does not report it' => [[]],
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    #[DataProvider('itemsThatAreAlreadyPublished')]
    public function test_items_already_published_or_without_a_date_have_no_badge(array $extra): void
    {
        $this->fakeList($extra);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            ->assertSee('School trip')
            ->assertDontSee('Publishes');
    }
}
