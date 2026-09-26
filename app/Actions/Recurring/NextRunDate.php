<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use Illuminate\Support\Carbon;

/**
 * The first occurrence of day_of_month on or after both startsOn and today
 * (never schedules a run in the past), clamped to the last day of a shorter
 * month (e.g. day 31 in February).
 */
class NextRunDate
{
    public function __invoke(int $dayOfMonth, Carbon $startsOn): Carbon
    {
        $today = Carbon::today();
        $reference = $today->greaterThan($startsOn) ? $today : $startsOn->copy()->startOfDay();

        $candidate = $reference->copy()->startOfMonth();
        $candidate = $candidate->day(min($dayOfMonth, $candidate->daysInMonth));

        if ($candidate->lt($reference)) {
            $nextMonth = $reference->copy()->addMonthNoOverflow()->startOfMonth();
            $candidate = $nextMonth->day(min($dayOfMonth, $nextMonth->daysInMonth));
        }

        return $candidate;
    }
}
