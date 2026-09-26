<?php

declare(strict_types=1);

namespace App\Actions\Recurring;

use App\Actions\ApiActionResult;
use App\Models\RecurringExpense;

class ToggleRecurringExpense
{
    public function __invoke(RecurringExpense $recurringExpense): ApiActionResult
    {
        $recurringExpense->update(['active' => ! $recurringExpense->active]);

        return ApiActionResult::success();
    }
}
