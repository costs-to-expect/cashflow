<?php

declare(strict_types=1);

namespace App\Service\Api;

/**
 * The single entry point for talking to the Costs to Expect API. Bound as a
 * singleton per-request by ApiServiceProvider, with the bearer token for the
 * signed-in user (if any) already wired in.
 */
class ApiService
{
    private Http $http;

    /**
     * resources() gets called independently by the nav composer and by
     * whatever page is rendering (dashboard, expense form, ...) - memoized
     * so a single request only ever fetches it once.
     */
    private ?array $resourcesCache = null;

    public function __construct(?string $bearer = null)
    {
        $this->http = new Http($bearer);
    }

    public function signIn(string $email, string $password): array
    {
        return $this->http->post(Uri::signIn(), [
            'email' => $email,
            'password' => $password,
            'device_name' => 'costs-to-expect-expense',
        ]);
    }

    public function currentUser(): array
    {
        return $this->http->get(Uri::authUser());
    }

    public function currencies(): array
    {
        return $this->http->get(Uri::currencies());
    }

    public function resources(): array
    {
        return $this->resourcesCache ??= $this->http->get(Uri::resources());
    }

    public function resource(string $resourceId): array
    {
        return $this->http->get(Uri::resource($resourceId));
    }

    public function createResource(string $name, string $description): array
    {
        return $this->http->post(Uri::resources(), [
            'name' => $name,
            'description' => $description,
            'item_subtype_id' => config('app.api.item_subtype_id'),
        ]);
    }

    public function items(string $resourceId, array $query = []): array
    {
        return $this->http->get(Uri::items($resourceId, $query));
    }

    /**
     * Count + subtotal per currency for the given resource, optionally
     * filtered (e.g. 'filter' => 'effective_date:2026-04-06:2027-04-05') -
     * computed server-side, so summing a whole reporting period doesn't
     * mean paging through every matching item.
     *
     * Known API issue (ticket filed): the filtered form returns an empty
     * result for items created moments earlier on a brand new resource,
     * suspected to be a scheduler/cache job not running locally - works
     * correctly on established data.
     *
     * @param  array<string, mixed>  $query
     */
    public function itemsSummary(string $resourceId, array $query = []): array
    {
        return $this->http->get(Uri::itemsSummary($resourceId, $query));
    }

    /**
     * itemsSummary(), aggregated across every resource under the resource
     * type at once - for a resource-type-wide total (e.g. all children's
     * expenses combined) rather than one child's.
     *
     * @param  array<string, mixed>  $query
     */
    public function resourceTypeItemsSummary(array $query = []): array
    {
        return $this->http->get(Uri::resourceTypeItemsSummary($query));
    }

    public function item(string $resourceId, string $itemId): array
    {
        return $this->http->get(Uri::item($resourceId, $itemId));
    }

    public function createItem(string $resourceId, array $payload): array
    {
        return $this->http->post(Uri::items($resourceId), $payload);
    }

    public function updateItem(string $resourceId, string $itemId, array $payload): array
    {
        return $this->http->patch(Uri::item($resourceId, $itemId), $payload);
    }

    public function deleteItem(string $resourceId, string $itemId): array
    {
        return $this->http->delete(Uri::item($resourceId, $itemId));
    }

    public function categories(): array
    {
        return $this->http->get(Uri::categories());
    }

    public function createCategory(string $name, string $description): array
    {
        return $this->http->post(Uri::categories(), ['name' => $name, 'description' => $description]);
    }

    public function updateCategory(string $categoryId, string $name, string $description): array
    {
        return $this->http->patch(Uri::category($categoryId), ['name' => $name, 'description' => $description]);
    }

    public function subcategories(string $categoryId): array
    {
        return $this->http->get(Uri::subcategories($categoryId));
    }

    public function createSubcategory(string $categoryId, string $name, string $description): array
    {
        return $this->http->post(Uri::subcategories($categoryId), ['name' => $name, 'description' => $description]);
    }

    public function updateSubcategory(string $categoryId, string $subcategoryId, string $name, string $description): array
    {
        return $this->http->patch(Uri::subcategory($categoryId, $subcategoryId), ['name' => $name, 'description' => $description]);
    }

    public function itemCategories(string $resourceId, string $itemId): array
    {
        return $this->http->get(Uri::itemCategories($resourceId, $itemId));
    }

    public function assignItemCategory(string $resourceId, string $itemId, string $categoryId): array
    {
        return $this->http->post(Uri::itemCategories($resourceId, $itemId), ['category_id' => $categoryId]);
    }

    public function deleteItemCategory(string $resourceId, string $itemId, string $itemCategoryId): array
    {
        return $this->http->delete(Uri::itemCategory($resourceId, $itemId, $itemCategoryId));
    }

    public function itemSubcategories(string $resourceId, string $itemId, string $itemCategoryId): array
    {
        return $this->http->get(Uri::itemSubcategories($resourceId, $itemId, $itemCategoryId));
    }

    public function assignItemSubcategory(string $resourceId, string $itemId, string $itemCategoryId, string $subcategoryId): array
    {
        return $this->http->post(Uri::itemSubcategories($resourceId, $itemId, $itemCategoryId), ['subcategory_id' => $subcategoryId]);
    }

    public function deleteItemSubcategory(string $resourceId, string $itemId, string $itemCategoryId, string $itemSubcategoryId): array
    {
        return $this->http->delete(Uri::itemSubcategory($resourceId, $itemId, $itemCategoryId, $itemSubcategoryId));
    }
}
