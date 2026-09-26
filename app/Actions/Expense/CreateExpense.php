<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

/**
 * Creates an allocated-expense item for every allocation given. A plain
 * (non-split) expense is just a single allocation at 100%. A split expense
 * is one row per resource - each gets its own item on the API sharing the
 * same name/description/date/total, differing only in its percentage share.
 *
 * We verified against a real (throwaway) resource type on the local dev API
 * that the API's own partial-transfer endpoint does *not* make a shared
 * expense show up in the receiving resource's own item list or summary, so
 * it isn't used here - one item per resource is the only way every
 * resource's lists/totals actually reflect its share.
 */
class CreateExpense
{
    public function __construct(private readonly ApiService $api) {}

    /**
     * @param  array{name: string, description: ?string, effective_date: string, currency_id: string, total: string, category_id?: ?string, subcategory_id?: ?string}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(array $expense, array $allocations): ApiActionResult
    {
        $created = [];

        // The API requires "0.00" formatting - a decimal column round-tripped
        // through SQLite (no fixed-point storage) can come back as "20".
        $total = number_format((float) $expense['total'], 2, '.', '');

        foreach ($allocations as $allocation) {
            $payload = [
                'name' => $expense['name'],
                'description' => $expense['description'],
                'effective_date' => $expense['effective_date'],
                'currency_id' => $expense['currency_id'],
                'total' => $total,
            ];

            // The API defaults percentage to 100 when it's not sent.
            if ((int) $allocation['percentage'] !== 100) {
                $payload['percentage'] = $allocation['percentage'];
            }

            $response = $this->api->createItem($allocation['resource_id'], $payload);

            if ($response['status'] !== 200 && $response['status'] !== 201) {
                if ($response['status'] === 422) {
                    return ApiActionResult::validationFailed($response['fields']);
                }

                return ApiActionResult::failed(
                    "Failed to create the expense for resource {$allocation['resource_id']}, ".
                    "created {$this->countCreated($created)} of ".count($allocations).' so far.'
                );
            }

            $itemId = $response['content']['id'];
            $created[$allocation['resource_id']] = $itemId;

            if (! empty($expense['category_id'])) {
                $this->assignCategory($allocation['resource_id'], $itemId, $expense['category_id'], $expense['subcategory_id'] ?? null);
            }
        }

        return ApiActionResult::success(['items' => $created]);
    }

    /**
     * Category/subcategory assignment is supplementary - if it fails the
     * expense item itself has already been created successfully, so we log
     * rather than failing the whole action.
     */
    private function assignCategory(string $resourceId, string $itemId, string $categoryId, ?string $subcategoryId): void
    {
        $assigned = $this->api->assignItemCategory($resourceId, $itemId, $categoryId);

        if (! in_array($assigned['status'], [200, 201], true)) {
            report(new \RuntimeException("Failed to assign category {$categoryId} to item {$itemId}: status {$assigned['status']}"));

            return;
        }

        if ($subcategoryId !== null) {
            $itemCategoryId = $assigned['content']['id'];
            $assignedSub = $this->api->assignItemSubcategory($resourceId, $itemId, $itemCategoryId, $subcategoryId);

            if (! in_array($assignedSub['status'], [200, 201], true)) {
                report(new \RuntimeException("Failed to assign subcategory {$subcategoryId} to item {$itemId}: status {$assignedSub['status']}"));
            }
        }
    }

    private function countCreated(array $created): int
    {
        return count($created);
    }
}
