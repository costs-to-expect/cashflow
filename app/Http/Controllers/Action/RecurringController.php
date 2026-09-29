<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Recurring\CreateRecurringExpense;
use App\Actions\Recurring\DeleteRecurringExpense;
use App\Actions\Recurring\ToggleRecurringExpense;
use App\Actions\Recurring\UpdateRecurringExpense;
use App\Http\Controllers\Controller;
use App\Models\RecurringExpense;
use App\Models\ResourceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecurringController extends Controller
{
    public function store(Request $request, ResourceType $resourceType, CreateRecurringExpense $createRecurringExpense): RedirectResponse
    {
        $validated = $this->validated($request);

        $result = $createRecurringExpense(
            $resourceType,
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_id' => $validated['currency_id'],
                'total' => $validated['total'],
                'category_id' => $validated['category_id'] ?: null,
                'subcategory_id' => $validated['subcategory_id'] ?: null,
                'day_of_month' => $validated['day_of_month'],
                'starts_on' => $validated['starts_on'],
                'ends_on' => $validated['ends_on'] ?? null,
            ],
            $validated['allocations'],
        );

        return $this->redirectForApiResult($result, 'recurring.index', ['resourceType' => $resourceType], "{$validated['name']} will now repeat monthly.");
    }

    public function update(Request $request, ResourceType $resourceType, RecurringExpense $recurringExpense, UpdateRecurringExpense $updateRecurringExpense): RedirectResponse
    {
        abort_unless($recurringExpense->resource_type_id === $resourceType->id, 404);

        $validated = $this->validated($request);

        $result = $updateRecurringExpense(
            $recurringExpense,
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_id' => $validated['currency_id'],
                'total' => $validated['total'],
                'category_id' => $validated['category_id'] ?: null,
                'subcategory_id' => $validated['subcategory_id'] ?: null,
                'day_of_month' => $validated['day_of_month'],
                'starts_on' => $validated['starts_on'],
                'ends_on' => $validated['ends_on'] ?? null,
            ],
            $validated['allocations'],
        );

        return $this->redirectForApiResult($result, 'recurring.index', ['resourceType' => $resourceType], "{$validated['name']} has been updated.");
    }

    public function toggle(ResourceType $resourceType, RecurringExpense $recurringExpense, ToggleRecurringExpense $toggleRecurringExpense): RedirectResponse
    {
        abort_unless($recurringExpense->resource_type_id === $resourceType->id, 404);

        $toggleRecurringExpense($recurringExpense);

        return redirect()->route('recurring.index', $resourceType)->with('status', $recurringExpense->active ? "{$recurringExpense->name} resumed." : "{$recurringExpense->name} paused.");
    }

    public function destroy(ResourceType $resourceType, RecurringExpense $recurringExpense, DeleteRecurringExpense $deleteRecurringExpense): RedirectResponse
    {
        abort_unless($recurringExpense->resource_type_id === $resourceType->id, 404);

        $name = $recurringExpense->name;
        $deleteRecurringExpense($recurringExpense);

        return redirect()->route('recurring.index', $resourceType)->with('status', "{$name} has been removed.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'currency_id' => ['required', 'string'],
            'total' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'category_id' => ['nullable', 'string'],
            'subcategory_id' => ['nullable', 'string'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.resource_id' => ['required', 'string'],
            'allocations.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
    }
}
