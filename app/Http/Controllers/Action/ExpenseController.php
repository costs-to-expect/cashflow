<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Expense\CreateExpense;
use App\Actions\Expense\DeleteExpense;
use App\Actions\Expense\UpdateExpense;
use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request, ResourceType $resourceType, CreateExpense $createExpense): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'effective_date' => ['required', 'date'],
            'currency_id' => ['required', 'string'],
            'total' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'category_id' => ['nullable', 'string'],
            'subcategory_id' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.resource_id' => ['required', 'string'],
            'allocations.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $createExpense(
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'effective_date' => $validated['effective_date'],
                'currency_id' => $validated['currency_id'],
                'total' => $validated['total'],
                'category_id' => $validated['category_id'] ?: null,
                'subcategory_id' => $validated['subcategory_id'] ?: null,
            ],
            $validated['allocations'],
        );

        $firstResourceId = $validated['allocations'][0]['resource_id'];

        return $this->redirectForApiResult(
            $result,
            'resources.show',
            ['resourceType' => $resourceType, 'resource_id' => $firstResourceId],
            count($validated['allocations']) > 1
                ? "{$validated['name']} has been added and split across {$this->count($validated)} ".strtolower(config('app.api.resource_term_plural')).'.'
                : "{$validated['name']} has been added.",
        );
    }

    public function update(Request $request, ResourceType $resourceType, string $resource_id, string $item_id, UpdateExpense $updateExpense): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'effective_date' => ['required', 'date'],
            'currency_id' => ['required', 'string'],
            'total' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'category_id' => ['nullable', 'string'],
            'subcategory_id' => ['nullable', 'string'],
        ]);

        $validated['category_id'] = $validated['category_id'] ?: null;
        $validated['subcategory_id'] = $validated['subcategory_id'] ?: null;

        $result = $updateExpense($resource_id, $item_id, $validated);

        return $this->redirectForApiResult(
            $result,
            'resources.show',
            ['resourceType' => $resourceType, 'resource_id' => $resource_id],
            "{$validated['name']} has been updated.",
        );
    }

    public function destroy(ResourceType $resourceType, string $resource_id, string $item_id, DeleteExpense $deleteExpense): RedirectResponse
    {
        $result = $deleteExpense($resource_id, $item_id);

        return $this->redirectForApiResult($result, 'resources.show', ['resourceType' => $resourceType, 'resource_id' => $resource_id], 'The expense has been deleted.');
    }

    private function count(array $validated): int
    {
        return count($validated['allocations']);
    }
}
