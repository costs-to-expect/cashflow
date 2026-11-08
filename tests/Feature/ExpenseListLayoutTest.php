<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http as HttpFacade;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * The columns of a resource's expense list from md up: date, name, category
 * (only while categories are on) and amount. Which classes are there is all
 * a test can say about a layout, so these check the pieces that depend on the
 * data: the grid's columns, the category cell, and the line under the name
 * that stands in for the date and category below md.
 */
class ExpenseListLayoutTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    private const RT = 'api.test/v3/resource-types/rt-1';

    private const FOUR_COLUMNS = 'md:grid-cols-[5.5rem_minmax(0,1fr)_10rem_7.5rem]';

    private const THREE_COLUMNS = 'md:grid-cols-[5.5rem_minmax(0,1fr)_7.5rem]';

    /** The date cell and the category cell share these classes. */
    private const CELL = 'class="hidden pt-0.5 text-sm text-gray-500 md:block"';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function listing(array $extra = []): void
    {
        $this->fakeApi([self::RT.'/resources/r-1/items*' => HttpFacade::response([[
            'id' => 'i-1',
            'name' => 'Rent for Ada',
            'description' => null,
            'effective_date' => '2026-09-01',
            'currency' => ['code' => 'GBP'],
            'actualised_total' => '50.00',
            'total' => '50.00',
            'percentage' => 100,
            ...$extra,
        ]], 200)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function categorised(): array
    {
        return ['categories' => [['id' => 'c-2', 'name' => 'School', 'subcategories' => [['id' => 's-2', 'name' => 'Trips']]]]];
    }

    private function page(): string
    {
        return $this->signedIn()->get('/resource-types/rt-1/resources/r-1')->assertOk()->getContent();
    }

    public function test_the_rows_have_a_category_column_while_categories_are_on(): void
    {
        $this->listing($this->categorised());

        $html = $this->page();

        $this->assertStringContainsString(self::FOUR_COLUMNS, $html);
        $this->assertStringNotContainsString(self::THREE_COLUMNS, $html);

        // The date and the category each have a cell, and the category's is in it.
        $this->assertSame(2, substr_count($html, self::CELL));
        $this->assertMatchesRegularExpression('/'.preg_quote(self::CELL, '/').'>\s*School &rsaquo; Trips\s*<\/p>/', $html);
    }

    public function test_an_uncategorised_row_keeps_its_empty_category_cell_so_the_amount_stays_in_its_column(): void
    {
        $this->listing();

        $html = $this->page();

        $this->assertStringContainsString(self::FOUR_COLUMNS, $html);
        $this->assertSame(2, substr_count($html, self::CELL));
    }

    public function test_the_rows_have_no_category_column_while_categories_are_off(): void
    {
        $this->resourceType->setCategoriesEnabled(false);
        // However the API answers, what it says about categories isn't shown.
        $this->listing($this->categorised());

        $html = $this->page();

        $this->assertStringContainsString(self::THREE_COLUMNS, $html);
        $this->assertStringNotContainsString(self::FOUR_COLUMNS, $html);
        $this->assertSame(1, substr_count($html, self::CELL));
        $this->assertStringNotContainsString('School', $html);
    }

    public function test_the_date_and_category_are_also_on_the_line_under_the_name_for_small_screens(): void
    {
        $this->listing($this->categorised());

        $this->assertMatchesRegularExpression(
            '/<p class="mt-0.5 text-xs text-gray-500 md:hidden">\s*Tue 1 Sep\s*· School\s*&rsaquo; Trips\s*<\/p>/',
            $this->page()
        );
    }

    public function test_a_held_back_expense_says_so_beside_the_name_and_on_the_small_screen_line(): void
    {
        $this->listing(['publish_after' => '2026-11-01']);

        $html = $this->page();

        // The test's date is 2026-09-30, so the 1 November date is in the future.
        $this->assertSame(2, substr_count($html, 'Publishes 1 Nov'));
        $this->assertSame(1, substr_count($html, 'md:inline">Publishes 1 Nov'));
    }

    public function test_an_expense_that_is_already_published_has_no_badge(): void
    {
        $this->listing(['publish_after' => '2026-09-01']);

        $this->assertStringNotContainsString('Publishes', $this->page());
    }

    public function test_each_row_has_its_actions_in_the_name_column(): void
    {
        $this->listing();

        $this->assertMatchesRegularExpression(
            '/<div class="-mb-2 -ml-2 mt-1 flex flex-wrap text-sm">\s*<a [^>]*>Edit<\/a>\s*<a [^>]*>Copy<\/a>/',
            $this->page()
        );
    }
}
