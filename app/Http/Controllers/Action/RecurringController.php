<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Recurring\CreateRecurringExpense;
use App\Actions\Recurring\DeleteRecurringExpense;
use App\Actions\Recurring\ToggleRecurringExpense;
use App\Actions\Recurring\UpdateRecurringExpense;
use App\Http\Controllers\Controller;
use App\Models\RecurringExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecurringController extends Controller
{
    public function store(Request $request, CreateRecurringExpense $createRecurringExpense): RedirectResponse
    {
        $validated = $this->validated($request);

        $result = $createRecurringExpense(
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_id' => $validated['currency_id'],
                'total' => $validated['total'],
                'day_of_month' => $validated['day_of_month'],
            ],
            $validated['allocations'],
        );

        return $this->redirectForApiResult($result, 'recurring.index', [], "{$validated['name']} will now repeat monthly.");
    }

    public function update(Request $request, RecurringExpense $recurringExpense, UpdateRecurringExpense $updateRecurringExpense): RedirectResponse
    {
        $validated = $this->validated($request);

        $result = $updateRecurringExpense(
            $recurringExpense,
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_id' => $validated['currency_id'],
                'total' => $validated['total'],
                'day_of_month' => $validated['day_of_month'],
            ],
            $validated['allocations'],
        );

        return $this->redirectForApiResult($result, 'recurring.index', [], "{$validated['name']} has been updated.");
    }

    public function toggle(RecurringExpense $recurringExpense, ToggleRecurringExpense $toggleRecurringExpense): RedirectResponse
    {
        $toggleRecurringExpense($recurringExpense);

        return redirect()->route('recurring.index')->with('status', $recurringExpense->active ? "{$recurringExpense->name} resumed." : "{$recurringExpense->name} paused.");
    }

    public function destroy(RecurringExpense $recurringExpense, DeleteRecurringExpense $deleteRecurringExpense): RedirectResponse
    {
        $name = $recurringExpense->name;
        $deleteRecurringExpense($recurringExpense);

        return redirect()->route('recurring.index')->with('status', "{$name} has been removed.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'currency_id' => ['required', 'string'],
            'total' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.resource_id' => ['required', 'string'],
            'allocations.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
    }
}
