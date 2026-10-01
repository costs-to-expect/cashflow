<?php

namespace Tests\Feature;

use App\Actions\Recurring\UpdateRecurringExpense;
use App\Http\Controllers\Controller;
use App\Models\RecurringExpense;
use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryHandlingTest extends TestCase
{
    use RefreshDatabase;

    private function resourceType(): ResourceType
    {
        return ResourceType::create([
            'user_id' => 'user-1',
            'name' => 'Kids',
            'description' => 'Kids',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => 'api-kids',
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);
    }

    private function recurringExpense(ResourceType $resourceType): RecurringExpense
    {
        return RecurringExpense::create([
            'resource_type_id' => $resourceType->id,
            'name' => 'Rent',
            'description' => null,
            'currency_id' => 'gbp',
            'total' => '100.00',
            'category_id' => 'category-1',
            'subcategory_id' => 'subcategory-1',
            'frequency' => 'monthly',
            'day_of_month' => 1,
            'starts_on' => '2026-01-01',
            'ends_on' => null,
            'next_run_date' => '2026-10-01',
            'active' => true,
        ]);
    }

    private function expense(array $extra = []): array
    {
        return [
            'name' => 'Rent',
            'description' => null,
            'currency_id' => 'gbp',
            'total' => '120.00',
            'day_of_month' => 1,
            'starts_on' => '2026-01-01',
            'ends_on' => null,
            ...$extra,
        ];
    }

    private function update(RecurringExpense $recurringExpense, array $expense): void
    {
        app(UpdateRecurringExpense::class)($recurringExpense, $expense, [['resource_id' => 'resource-1', 'percentage' => 100]]);
    }

    public function test_category_rules_require_both_fields_while_categories_are_on(): void
    {
        $rules = $this->categoryRules($this->resourceType());

        $this->assertSame(['required', 'string'], $rules['category_id']);
        $this->assertSame(['required', 'string'], $rules['subcategory_id']);
    }

    public function test_category_rules_are_empty_while_categories_are_off(): void
    {
        $resourceType = $this->resourceType();
        $resourceType->setCategoriesEnabled(false);

        $this->assertSame([], $this->categoryRules($resourceType));
    }

    public function test_updating_a_recurring_expense_keeps_its_stored_categories_when_none_are_sent(): void
    {
        $recurringExpense = $this->recurringExpense($this->resourceType());

        $this->update($recurringExpense, $this->expense());

        $recurringExpense->refresh();
        // Compared numerically: SQLite has no fixed-point storage, so 120.00 comes back as 120.
        $this->assertSame(120.0, (float) $recurringExpense->total);
        $this->assertSame('category-1', $recurringExpense->category_id);
        $this->assertSame('subcategory-1', $recurringExpense->subcategory_id);
    }

    public function test_updating_a_recurring_expense_replaces_its_categories_when_they_are_sent(): void
    {
        $recurringExpense = $this->recurringExpense($this->resourceType());

        $this->update($recurringExpense, $this->expense(['category_id' => 'category-2', 'subcategory_id' => 'subcategory-2']));

        $recurringExpense->refresh();
        $this->assertSame('category-2', $recurringExpense->category_id);
        $this->assertSame('subcategory-2', $recurringExpense->subcategory_id);
    }

    /**
     * categoryRules() is protected on the base controller - call it through a
     * throwaway subclass rather than a whole HTTP round trip.
     */
    private function categoryRules(ResourceType $resourceType): array
    {
        return (new class extends Controller
        {
            public function rules(ResourceType $resourceType): array
            {
                return $this->categoryRules($resourceType);
            }
        })->rules($resourceType);
    }
}
