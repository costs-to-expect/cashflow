<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use App\Actions\ApiActionResult;
use App\Models\RecurringExpense;
use Illuminate\Support\Facades\DB;

class UpdateRecurringExpense
{
    /**
     * @param  array{name: string, description: ?string, currency_id: string, total: string, day_of_month: int}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(RecurringExpense $recurringExpense, array $expense, array $allocations): ApiActionResult
    {
        DB::transaction(function () use ($recurringExpense, $expense, $allocations) {
            $recurringExpense->update([
                'name' => $expense['name'],
                'description' => $expense['description'],
                'currency_id' => $expense['currency_id'],
                'total' => $expense['total'],
                'day_of_month' => $expense['day_of_month'],
            ]);

            $recurringExpense->allocations()->delete();

            foreach ($allocations as $index => $allocation) {
                $recurringExpense->allocations()->create([
                    'resource_id' => $allocation['resource_id'],
                    'percentage' => $allocation['percentage'],
                    'sort_order' => $index,
                ]);
            }
        });

        return ApiActionResult::success();
    }
}
