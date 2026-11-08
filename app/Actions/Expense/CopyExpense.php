<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;

/**
 * Copies one expense (a single item, with its percentage) to another
 * resource of the same resource type, as is: the same name, description,
 * date, amount, publish-after date, category and subcategory. The copy form
 * (View\ExpenseController::copy()) is for changing something first.
 *
 * Unlike that form this can't prompt, so a source without a category is
 * copied without one, even while categories are on.
 */
class CopyExpense
{
    public function __construct(
        private readonly ApiService $api,
        private readonly CreateExpense $createExpense,
    ) {}

    public function __invoke(ResourceType $resourceType, string $resourceId, string $itemId, string $targetResourceId): ApiActionResult
    {
        $categoriesEnabled = $resourceType->categoriesEnabled();

        // Two waves, the second needing what the first returns: the expense,
        // its category assignment and the resources to check the target
        // against, then the expense's subcategory assignment.
        $first = $this->api->pool(function (RequestPool $pool) use ($resourceId, $itemId, $categoriesEnabled) {
            $pool->item('item', $resourceId, $itemId)->resources();

            if ($categoriesEnabled) {
                $pool->itemCategories('itemCategories', $resourceId, $itemId);
            }
        });

        if ($first['item']['status'] !== 200) {
            return ApiActionResult::failed("The expense {$itemId} could not be found to copy.");
        }

        $target = collect($this->content($first[RequestPool::RESOURCES]))
            ->first(fn (array $resource) => (string) $resource['id'] === $targetResourceId);

        if ($target === null || $targetResourceId === $resourceId) {
            return ApiActionResult::failed("Resource {$targetResourceId} is not another resource of this resource type to copy to.");
        }

        $item = $first['item']['content'];
        $itemCategory = $this->content($first['itemCategories'] ?? null)[0] ?? null;
        $itemSubcategory = null;

        if ($itemCategory !== null) {
            $second = $this->api->pool(fn (RequestPool $pool) => $pool->itemSubcategories('itemSubcategories', $resourceId, $itemId, $itemCategory['id']));

            $itemSubcategory = $this->content($second['itemSubcategories'])[0] ?? null;
        }

        $result = ($this->createExpense)(
            [
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'effective_date' => $item['effective_date'],
                'currency_id' => $item['currency']['id'],
                'total' => $item['total'],
                'publish_after' => $item['publish_after'] ?? null,
                'category_id' => $itemCategory['category']['id'] ?? null,
                'subcategory_id' => $itemSubcategory['subcategory']['id'] ?? null,
            ],
            [['resource_id' => $targetResourceId, 'percentage' => (int) ($item['percentage'] ?? 100)]],
        );

        if ($result->fieldErrors !== []) {
            // There's no form to show these on, they'd only be lost.
            return ApiActionResult::failed('The API refused the copy: '.json_encode($result->fieldErrors));
        }

        if (! $result->ok) {
            return $result;
        }

        return ApiActionResult::success([...$result->data, 'name' => $item['name'], 'target' => $target['name']]);
    }

    /**
     * What a pooled read returned, or nothing if it failed (or was never asked for).
     *
     * @return array<int|string, mixed>
     */
    private function content(?array $response): array
    {
        return $response !== null && $response['status'] === 200 ? $response['content'] : [];
    }
}
