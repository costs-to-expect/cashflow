<?php

declare(strict_types=1);

namespace App\Service\Api;

/**
 * The single entry point for talking to the Costs to Expect API. Bound as a
 * singleton per-request by ApiServiceProvider, with the bearer token and the
 * currently active resource type's ids (if any - some routes have none, e.g.
 * the resource-type picker/create screens) already wired in.
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

    public function __construct(
        ?string $bearer = null,
        private readonly ?string $resourceTypeId = null,
        private readonly ?string $itemSubtypeId = null,
    ) {
        $this->http = new Http($bearer);
    }

    public function signIn(string $email, string $password): array
    {
        return $this->http->post(Uri::signIn(), [
            'email' => $email,
            'password' => $password,
            'device_name' => 'costs-to-expect-cashflow',
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

    public function resourceTypes(): array
    {
        return $this->http->get(Uri::resourceTypes());
    }

    /**
     * The resource types the signed-in user currently has access to, per
     * the API's own permission system - used to decide which locally known
     * resource types are actually shown to them (not a local ownership
     * check, the API is the source of truth for who can see what).
     */
    public function permittedResourceTypes(): array
    {
        return $this->http->get(Uri::permittedResourceTypes());
    }

    public function createResourceType(string $name, string $description, string $itemTypeId): array
    {
        return $this->http->post(Uri::resourceTypes(), [
            'name' => $name,
            'description' => $description,
            'item_type_id' => $itemTypeId,
        ]);
    }

    public function itemTypes(): array
    {
        return $this->http->get(Uri::itemTypes());
    }

    public function itemSubtypes(string $itemTypeId): array
    {
        return $this->http->get(Uri::itemSubtypes($itemTypeId));
    }

    public function resources(): array
    {
        return $this->resourcesCache ??= $this->http->get(Uri::resources($this->resourceTypeId));
    }

    public function resource(string $resourceId): array
    {
        return $this->http->get(Uri::resource($this->resourceTypeId, $resourceId));
    }

    public function createResource(string $name, string $description): array
    {
        return $this->http->post(Uri::resources($this->resourceTypeId), [
            'name' => $name,
            'description' => $description,
            'item_subtype_id' => $this->itemSubtypeId,
        ]);
    }

    public function items(string $resourceId, array $query = []): array
    {
        return $this->http->get(Uri::items($this->resourceTypeId, $resourceId, $query));
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
        return $this->http->get(Uri::itemsSummary($this->resourceTypeId, $resourceId, $query));
    }

    /**
     * itemsSummary(), aggregated across every resource under the resource
     * type at once - for a resource-type-wide total (e.g. every resource's
     * expenses combined) rather than one resource's.
     *
     * @param  array<string, mixed>  $query
     */
    public function resourceTypeItemsSummary(array $query = []): array
    {
        return $this->http->get(Uri::resourceTypeItemsSummary($this->resourceTypeId, $query));
    }

    public function item(string $resourceId, string $itemId): array
    {
        return $this->http->get(Uri::item($this->resourceTypeId, $resourceId, $itemId));
    }

    public function createItem(string $resourceId, array $payload): array
    {
        return $this->http->post(Uri::items($this->resourceTypeId, $resourceId), $payload);
    }

    public function updateItem(string $resourceId, string $itemId, array $payload): array
    {
        return $this->http->patch(Uri::item($this->resourceTypeId, $resourceId, $itemId), $payload);
    }

    public function deleteItem(string $resourceId, string $itemId): array
    {
        return $this->http->delete(Uri::item($this->resourceTypeId, $resourceId, $itemId));
    }

    public function categories(): array
    {
        return $this->http->get(Uri::categories($this->resourceTypeId));
    }

    public function createCategory(string $name, string $description): array
    {
        return $this->http->post(Uri::categories($this->resourceTypeId), ['name' => $name, 'description' => $description]);
    }

    public function updateCategory(string $categoryId, string $name, string $description): array
    {
        return $this->http->patch(Uri::category($this->resourceTypeId, $categoryId), ['name' => $name, 'description' => $description]);
    }

    public function subcategories(string $categoryId): array
    {
        return $this->http->get(Uri::subcategories($this->resourceTypeId, $categoryId));
    }

    public function createSubcategory(string $categoryId, string $name, string $description): array
    {
        return $this->http->post(Uri::subcategories($this->resourceTypeId, $categoryId), ['name' => $name, 'description' => $description]);
    }

    public function updateSubcategory(string $categoryId, string $subcategoryId, string $name, string $description): array
    {
        return $this->http->patch(Uri::subcategory($this->resourceTypeId, $categoryId, $subcategoryId), ['name' => $name, 'description' => $description]);
    }

    public function itemCategories(string $resourceId, string $itemId): array
    {
        return $this->http->get(Uri::itemCategories($this->resourceTypeId, $resourceId, $itemId));
    }

    public function assignItemCategory(string $resourceId, string $itemId, string $categoryId): array
    {
        return $this->http->post(Uri::itemCategories($this->resourceTypeId, $resourceId, $itemId), ['category_id' => $categoryId]);
    }

    public function deleteItemCategory(string $resourceId, string $itemId, string $itemCategoryId): array
    {
        return $this->http->delete(Uri::itemCategory($this->resourceTypeId, $resourceId, $itemId, $itemCategoryId));
    }

    public function itemSubcategories(string $resourceId, string $itemId, string $itemCategoryId): array
    {
        return $this->http->get(Uri::itemSubcategories($this->resourceTypeId, $resourceId, $itemId, $itemCategoryId));
    }

    public function assignItemSubcategory(string $resourceId, string $itemId, string $itemCategoryId, string $subcategoryId): array
    {
        return $this->http->post(Uri::itemSubcategories($this->resourceTypeId, $resourceId, $itemId, $itemCategoryId), ['subcategory_id' => $subcategoryId]);
    }

    public function deleteItemSubcategory(string $resourceId, string $itemId, string $itemCategoryId, string $itemSubcategoryId): array
    {
        return $this->http->delete(Uri::itemSubcategory($this->resourceTypeId, $resourceId, $itemId, $itemCategoryId, $itemSubcategoryId));
    }
}
