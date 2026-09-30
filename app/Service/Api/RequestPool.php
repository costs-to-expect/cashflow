<?php

declare(strict_types=1);

namespace App\Service\Api;

/**
 * Collects the requests for ApiService::pool(), keyed so the caller can pick
 * each response back out of the result. Mirrors ApiService's read methods
 * (same names, scoped to the same resource type) - a method is added here
 * as a page needs to pool that call, anything else can go in with get() or
 * head() and a URI built from Uri.
 *
 * The calls a page only ever makes once - resources(), permittedResourceTypes(),
 * currencies() and categories() - have fixed keys (the constants below, and
 * ApiService memoizes the first two). Every other method takes the key to
 * file its response under, as a page can pool several of the same call
 * (e.g. one items() per resource).
 */
class RequestPool
{
    public const RESOURCES = 'resources';

    public const PERMITTED_RESOURCE_TYPES = 'permittedResourceTypes';

    public const CURRENCIES = 'currencies';

    public const CATEGORIES = 'categories';

    /** @var array<string, PoolRequest> */
    private array $requests = [];

    public function __construct(private readonly ?string $resourceTypeId = null) {}

    public function get(string $key, string $uri): static
    {
        $this->requests[$key] = PoolRequest::get($uri);

        return $this;
    }

    public function head(string $key, string $uri): static
    {
        $this->requests[$key] = PoolRequest::head($uri);

        return $this;
    }

    public function resources(): static
    {
        return $this->get(self::RESOURCES, Uri::resources($this->resourceTypeId));
    }

    public function permittedResourceTypes(): static
    {
        return $this->get(self::PERMITTED_RESOURCE_TYPES, Uri::permittedResourceTypes());
    }

    /**
     * What the layout's nav needs on every page (see AppServiceProvider), so
     * a page that pools can fetch it along with its own requests rather than
     * the layout fetching it afterwards. A route outside a resource type has
     * no resources to list.
     */
    public function navigation(bool $withResources = true): static
    {
        if ($withResources) {
            $this->resources();
        }

        return $this->permittedResourceTypes();
    }

    public function currencies(): static
    {
        return $this->get(self::CURRENCIES, Uri::currencies());
    }

    public function categories(): static
    {
        return $this->get(self::CATEGORIES, Uri::categories($this->resourceTypeId));
    }

    public function subcategories(string $key, string $categoryId): static
    {
        return $this->get($key, Uri::subcategories($this->resourceTypeId, $categoryId));
    }

    public function resource(string $key, string $resourceId): static
    {
        return $this->get($key, Uri::resource($this->resourceTypeId, $resourceId));
    }

    public function item(string $key, string $resourceId, string $itemId): static
    {
        return $this->get($key, Uri::item($this->resourceTypeId, $resourceId, $itemId));
    }

    public function itemCategories(string $key, string $resourceId, string $itemId): static
    {
        return $this->get($key, Uri::itemCategories($this->resourceTypeId, $resourceId, $itemId));
    }

    public function itemSubcategories(string $key, string $resourceId, string $itemId, string $itemCategoryId): static
    {
        return $this->get($key, Uri::itemSubcategories($this->resourceTypeId, $resourceId, $itemId, $itemCategoryId));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function items(string $key, string $resourceId, array $query = []): static
    {
        return $this->get($key, Uri::items($this->resourceTypeId, $resourceId, $query));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function itemsSummary(string $key, string $resourceId, array $query = []): static
    {
        return $this->get($key, Uri::itemsSummary($this->resourceTypeId, $resourceId, $query));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function resourceTypeItemsSummary(string $key, array $query = []): static
    {
        return $this->get($key, Uri::resourceTypeItemsSummary($this->resourceTypeId, $query));
    }

    /**
     * @return array<string, PoolRequest>
     */
    public function requests(): array
    {
        return $this->requests;
    }
}
