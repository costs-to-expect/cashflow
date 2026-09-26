<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

/**
 * Creates an allocated-expense item for every allocation given. A plain
 * (non-split) expense is just a single allocation at 100%. A split expense
 * is one row per child - each gets its own item on the API sharing the same
 * name/description/date/total, differing only in its percentage share.
 *
 * We verified against a real (throwaway) resource type on the local dev API
 * that the API's own partial-transfer endpoint does *not* make a shared
 * expense show up in the receiving resource's own item list or summary, so
 * it isn't used here - one item per child is the only way both children's
 * lists/totals actually reflect their share.
 */
class CreateExpense
{
    public function __construct(private readonly ApiService $api) {}

    /**
     * @param  array{name: string, description: ?string, effective_date: string, currency_id: string, total: string}  $expense
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(array $expense, array $allocations): ApiActionResult
    {
        $created = [];

        // The API requires "0.00" formatting - a decimal column round-tripped
        // through SQLite (no fixed-point storage) can come back as "20".
        $total = number_format((float) $expense['total'], 2, '.', '');

        foreach ($allocations as $allocation) {
            $response = $this->api->createItem($allocation['resource_id'], [
                'name' => $expense['name'],
                'description' => $expense['description'],
                'effective_date' => $expense['effective_date'],
                'currency_id' => $expense['currency_id'],
                'total' => $total,
                'percentage' => $allocation['percentage'],
            ]);

            if ($response['status'] !== 200 && $response['status'] !== 201) {
                if ($response['status'] === 422) {
                    return ApiActionResult::validationFailed($response['fields']);
                }

                return ApiActionResult::failed(
                    "Failed to create the expense for resource {$allocation['resource_id']}, ".
                    "created {$this->countCreated($created)} of ".count($allocations).' so far.'
                );
            }

            $created[$allocation['resource_id']] = $response['content']['id'];
        }

        return ApiActionResult::success(['items' => $created]);
    }

    private function countCreated(array $created): int
    {
        return count($created);
    }
}
