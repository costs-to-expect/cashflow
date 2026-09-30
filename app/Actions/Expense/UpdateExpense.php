<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

class UpdateExpense
{
    public function __construct(private readonly ApiService $api) {}

    /**
     * Category/subcategory are only synced when the payload carries them. A
     * resource type with categories turned off doesn't send them, and an
     * absent key must leave whatever the item already has untouched - not
     * clear it - so turning categories back on finds them still there.
     */
    public function __invoke(string $resourceId, string $itemId, array $payload): ApiActionResult
    {
        $syncCategories = array_key_exists('category_id', $payload);
        $categoryId = $payload['category_id'] ?? null;
        $subcategoryId = $payload['subcategory_id'] ?? null;
        unset($payload['category_id'], $payload['subcategory_id']);

        if (array_key_exists('total', $payload)) {
            $payload['total'] = number_format((float) $payload['total'], 2, '.', '');
        }

        // The API defaults percentage to 100 when it's not sent.
        if (array_key_exists('percentage', $payload) && (int) $payload['percentage'] === 100) {
            unset($payload['percentage']);
        }

        $response = $this->api->updateItem($resourceId, $itemId, $payload);

        if ($response['status'] !== 204) {
            if ($response['status'] === 422) {
                return ApiActionResult::validationFailed($response['fields']);
            }

            return ApiActionResult::failed('Unexpected status '.$response['status'].' updating the expense.');
        }

        if ($syncCategories) {
            $this->syncCategory($resourceId, $itemId, $categoryId, $subcategoryId);
        }

        return ApiActionResult::success();
    }

    /**
     * Category assignment is a separate join resource on the API, capped at
     * one category (and one subcategory) per item - so changing it here
     * means dropping any existing assignment before creating the new one.
     */
    private function syncCategory(string $resourceId, string $itemId, ?string $categoryId, ?string $subcategoryId): void
    {
        $existing = $this->api->itemCategories($resourceId, $itemId);
        $current = $existing['status'] === 200 ? ($existing['content'][0] ?? null) : null;
        $currentCategoryId = $current['category']['id'] ?? null;
        $currentItemCategoryId = $current['id'] ?? null;

        if ($categoryId === $currentCategoryId) {
            if ($currentItemCategoryId !== null) {
                $this->syncSubcategory($resourceId, $itemId, $currentItemCategoryId, $subcategoryId);
            }

            return;
        }

        if ($currentItemCategoryId !== null) {
            // A subcategory assignment is a foreign key against this item-category
            // row, so it has to go first or the API rejects the delete (409).
            $this->syncSubcategory($resourceId, $itemId, $currentItemCategoryId, null);
            $this->api->deleteItemCategory($resourceId, $itemId, $currentItemCategoryId);
        }

        if ($categoryId === null) {
            return;
        }

        $assigned = $this->api->assignItemCategory($resourceId, $itemId, $categoryId);

        if (in_array($assigned['status'], [200, 201], true) && $subcategoryId !== null) {
            $this->api->assignItemSubcategory($resourceId, $itemId, $assigned['content']['id'], $subcategoryId);
        }
    }

    private function syncSubcategory(string $resourceId, string $itemId, string $itemCategoryId, ?string $subcategoryId): void
    {
        $existing = $this->api->itemSubcategories($resourceId, $itemId, $itemCategoryId);
        $current = $existing['status'] === 200 ? ($existing['content'][0] ?? null) : null;
        $currentSubcategoryId = $current['subcategory']['id'] ?? null;
        $currentItemSubcategoryId = $current['id'] ?? null;

        if ($subcategoryId === $currentSubcategoryId) {
            return;
        }

        if ($currentItemSubcategoryId !== null) {
            $this->api->deleteItemSubcategory($resourceId, $itemId, $itemCategoryId, $currentItemSubcategoryId);
        }

        if ($subcategoryId !== null) {
            $this->api->assignItemSubcategory($resourceId, $itemId, $itemCategoryId, $subcategoryId);
        }
    }
}
