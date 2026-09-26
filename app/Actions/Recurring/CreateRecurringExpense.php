<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use App\Actions\ApiActionResult;
use App\Models\RecurringExpense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CreateRecurringExpense
{
    /**
     * @param  array{name: string, description: ?string, currency_id: string, total: string, day_of_month: int}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(array $expense, array $allocations): ApiActionResult
    {
        $dayOfMonth = (int) $expense['day_of_month'];
        $today = Carbon::today();

        $nextRunDate = $today->copy()->startOfMonth()->day(min($dayOfMonth, $today->daysInMonth));

        if ($nextRunDate->lt($today)) {
            $followingMonth = $today->copy()->addMonthNoOverflow()->startOfMonth();
            $nextRunDate = $followingMonth->day(min($dayOfMonth, $followingMonth->daysInMonth));
        }

        $recurringExpense = DB::transaction(function () use ($expense, $allocations, $nextRunDate, $dayOfMonth) {
            $recurringExpense = RecurringExpense::create([
                'name' => $expense['name'],
                'description' => $expense['description'],
                'currency_id' => $expense['currency_id'],
                'total' => $expense['total'],
                'frequency' => 'monthly',
                'day_of_month' => $dayOfMonth,
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
