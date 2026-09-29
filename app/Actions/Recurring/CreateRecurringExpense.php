<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use App\Actions\ApiActionResult;
use App\Models\RecurringExpense;
use App\Models\ResourceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreateRecurringExpense
{
    public function __construct(private readonly NextRunDate $nextRunDate) {}

    /**
     * @param  array{name: string, description: ?string, currency_id: string, total: string, category_id: ?string, subcategory_id: ?string, day_of_month: int, starts_on: string, ends_on: ?string}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(ResourceType $resourceType, array $expense, array $allocations): ApiActionResult
    {
        $dayOfMonth = (int) $expense['day_of_month'];
        $startsOn = Carbon::parse($expense['starts_on']);

        $nextRunDate = ($this->nextRunDate)($dayOfMonth, $startsOn);

        $recurringExpense = DB::transaction(function () use ($resourceType, $expense, $allocations, $nextRunDate, $dayOfMonth, $startsOn) {
            $recurringExpense = RecurringExpense::create([
                'resource_type_id' => $resourceType->id,
                'name' => $expense['name'],
                'description' => $expense['description'],
                'currency_id' => $expense['currency_id'],
                'total' => $expense['total'],
                'category_id' => $expense['category_id'],
                'subcategory_id' => $expense['subcategory_id'],
                'frequency' => 'monthly',
                'day_of_month' => $dayOfMonth,
                'starts_on' => $startsOn,
                'ends_on' => $expense['ends_on'],
                'next_run_date' => $nextRunDate,
                'active' => true,
            ]);

            foreach ($allocations as $index => $allocation) {
                $recurringExpense->allocations()->create([
                    'resource_id' => $allocation['resource_id'],
                    'percentage' => $allocation['percentage'],
                    'sort_order' => $index,
                ]);
            }

            return $recurringExpense;
        });

        return ApiActionResult::success(['recurring_expense' => $recurringExpense]);
    }
}
