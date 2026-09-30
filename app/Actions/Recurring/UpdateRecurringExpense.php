<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use App\Actions\ApiActionResult;
use App\Models\RecurringExpense;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateRecurringExpense
{
    public function __construct(private readonly NextRunDate $nextRunDate) {}

    /**
     * @param  array{name: string, description: ?string, currency_id: string, total: string, category_id?: ?string, subcategory_id?: ?string, day_of_month: int, starts_on: string, ends_on: ?string}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(RecurringExpense $recurringExpense, array $expense, array $allocations): ApiActionResult
    {
        $dayOfMonth = (int) $expense['day_of_month'];
        $startsOn = Carbon::parse($expense['starts_on'])->startOfDay();

        // Only recompute next_run_date if the schedule itself changed -
        // otherwise an unrelated edit (amount, category, ...) would reset an
        // already-advanced schedule back to its first occurrence and risk
        // re-posting a run that's already happened.
        $scheduleChanged = $dayOfMonth !== $recurringExpense->day_of_month
            || ! $startsOn->isSameDay($recurringExpense->starts_on);

        $nextRunDate = $scheduleChanged
            ? ($this->nextRunDate)($dayOfMonth, $startsOn)
            : $recurringExpense->next_run_date;

        DB::transaction(function () use ($recurringExpense, $expense, $allocations, $nextRunDate, $dayOfMonth, $startsOn) {
            $recurringExpense->update([
                'name' => $expense['name'],
                'description' => $expense['description'],
                'currency_id' => $expense['currency_id'],
                'total' => $expense['total'],
                'day_of_month' => $dayOfMonth,
                'starts_on' => $startsOn,
                'ends_on' => $expense['ends_on'],
                'next_run_date' => $nextRunDate,
                // Absent keys (categories turned off) leave the stored values alone.
                ...Arr::only($expense, ['category_id', 'subcategory_id']),
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
