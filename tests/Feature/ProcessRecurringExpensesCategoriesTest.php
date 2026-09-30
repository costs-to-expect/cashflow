<?php

namespace Tests\Feature;

use App\Models\RecurringExpense;
use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessRecurringExpensesCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api.service_token' => 'service-token', 'app.api.base_url' => 'http://api.test']);

        Http::fake(fn () => Http::response(['id' => 'created-1'], 201));
    }

    private function dueTemplate(bool $categoriesEnabled): RecurringExpense
    {
        $resourceType = ResourceType::create([
            'user_id' => 'user-1',
            'name' => 'Kids',
            'description' => 'Kids',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => 'api-kids',
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);
        $resourceType->setCategoriesEnabled($categoriesEnabled);

        $recurringExpense = RecurringExpense::create([
            'resource_type_id' => $resourceType->id,
            'name' => 'Rent',
            'description' => null,
            'currency_id' => 'gbp',
            'total' => '100.00',
            'category_id' => 'category-1',
            'subcategory_id' => 'subcategory-1',
            'frequency' => 'monthly',
            'day_of_month' => 1,
            'starts_on' => now()->subMonths(2)->toDateString(),
            'ends_on' => null,
            'next_run_date' => now()->toDateString(),
            'active' => true,
        ]);

        $recurringExpense->allocations()->create(['resource_id' => 'resource-1', 'percentage' => 100, 'sort_order' => 0]);

        return $recurringExpense;
    }

    private function postedTo(string $suffix): callable
    {
        return fn (Request $request) => $request->method() === 'POST' && str_ends_with($request->url(), $suffix);
    }

    public function test_the_stored_category_is_assigned_while_categories_are_on(): void
    {
        $this->dueTemplate(true);

        $this->artisan('expense:process-recurring')->assertSuccessful();

        Http::assertSent($this->postedTo('/items'));
        Http::assertSent($this->postedTo('/categories'));
        Http::assertSent($this->postedTo('/subcategories'));
    }

    public function test_no_category_is_assigned_while_categories_are_off_and_the_template_keeps_it(): void
    {
        $recurringExpense = $this->dueTemplate(false);

        $this->artisan('expense:process-recurring')->assertSuccessful();

        Http::assertSent($this->postedTo('/items'));
        Http::assertNotSent($this->postedTo('/categories'));
        Http::assertNotSent($this->postedTo('/subcategories'));

        $recurringExpense->refresh();
        $this->assertSame('category-1', $recurringExpense->category_id);
        $this->assertSame('subcategory-1', $recurringExpense->subcategory_id);
    }
}
