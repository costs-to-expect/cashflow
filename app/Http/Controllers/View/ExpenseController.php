<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function create(Request $request, ResourceType $resourceType): View
    {
        // Two waves, the second needing what the first returns. The first is
        // the resources, currencies and categories (and the nav's needs)...
        $first = $this->api->pool(fn (RequestPool $pool) => $this->poolFormOptions($pool, $resourceType));

        $resources = $this->content($first[RequestPool::RESOURCES]);
        $currencies = $this->sortCurrenciesGbpFirst($this->content($first[RequestPool::CURRENCIES]));
        $categories = $this->content($first[RequestPool::CATEGORIES] ?? null);

        // ...the second each category's subcategories and, from each resource, its recent names.
        $second = $this->api->pool(function (RequestPool $pool) use ($resources, $categories) {
            $this->poolSubcategories($pool, $categories);
            $this->poolNameSuggestions($pool, $resources);
        });

        return view('expenses.create', [
            'resources' => $resources,
            'currencies' => $currencies,
            'defaultCurrencyId' => $this->resolveDefaultCurrencyId($currencies),
            'categoriesEnabled' => $resourceType->categoriesEnabled(),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories, $second),
            'preselectedResourceId' => $request->query('resource_id'),
            'defaultAllocations' => $this->defaultAllocationsFor($resources, $request->boolean('split'), $resourceType),
            'defaultSplit' => $this->defaultSplitAllocations($resourceType),
            'nameSuggestions' => $this->nameSuggestions($resources, $second),
        ]);
    }

    public function edit(ResourceType $resourceType, string $resource_id, string $item_id): View
    {
        $categoriesEnabled = $resourceType->categoriesEnabled();

        // Two waves, the second needing what the first returns. The first is
        // the expense itself and its category assignment, plus what the
        // create form's first wave fetches...
        $first = $this->api->pool(function (RequestPool $pool) use ($resourceType, $resource_id, $item_id, $categoriesEnabled) {
            $pool->item('item', $resource_id, $item_id);

            if ($categoriesEnabled) {
                $pool->itemCategories('itemCategories', $resource_id, $item_id);
            }

            $this->poolFormOptions($pool, $resourceType);
        });

        $item = $first['item'];

        abort_if($item['status'] !== 200, 404, 'That expense could not be found.');

        $resources = $this->content($first[RequestPool::RESOURCES]);
        $categories = $this->content($first[RequestPool::CATEGORIES] ?? null);
        $currentItemCategory = $this->content($first['itemCategories'] ?? null)[0] ?? null;

        // ...the second the expense's subcategory assignment (which needs its
        // category assignment's id), and what the create form's second does.
        $second = $this->api->pool(function (RequestPool $pool) use ($resource_id, $item_id, $resources, $categories, $currentItemCategory) {
            if ($currentItemCategory !== null) {
                $pool->itemSubcategories('itemSubcategories', $resource_id, $item_id, $currentItemCategory['id']);
            }

            $this->poolSubcategories($pool, $categories);
            $this->poolNameSuggestions($pool, $resources);
        });

        [$currentCategoryId, $currentSubcategoryId] = $this->currentCategorisation($currentItemCategory, $second);

        return view('expenses.edit', [
            'resourceId' => $resource_id,
            'item' => $item['content'],
            'resources' => $resources,
            'currencies' => $this->sortCurrenciesGbpFirst($this->content($first[RequestPool::CURRENCIES])),
            'categoriesEnabled' => $categoriesEnabled,
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories, $second),
            'currentCategoryId' => $currentCategoryId,
            'currentSubcategoryId' => $currentSubcategoryId,
            'nameSuggestions' => $this->nameSuggestions($resources, $second),
        ]);
    }

    /**
     * Each resource's recent expenses, for the "name" field's datalist (see
     * nameSuggestions() for reading them back).
     */
    private function poolNameSuggestions(RequestPool $pool, array $resources): void
    {
        foreach ($resources as $resource) {
            $pool->items('names.'.$resource['id'], $resource['id'], ['limit' => 100, 'sort' => 'effective_date:desc']);
        }
    }

    /**
     * Distinct names from recent expenses, for the "name" field's datalist.
     *
     * @param  array<string, array>  $responses  the pool poolNameSuggestions() was added to
     */
    private function nameSuggestions(array $resources, array $responses): array
    {
        $names = [];

        foreach ($resources as $resource) {
            foreach ($this->content($responses['names.'.$resource['id']]) as $item) {
                $names[$item['name']] = true;
            }
        }

        $names = array_keys($names);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return array_slice($names, 0, 150);
    }

    /**
     * @param  ?array<string, mixed>  $itemCategory  the expense's category assignment, if it has one
     * @param  array<string, array>  $responses  the pool its subcategory assignment was requested in
     * @return array{0: ?string, 1: ?string} [currentCategoryId, currentSubcategoryId]
     */
    private function currentCategorisation(?array $itemCategory, array $responses): array
    {
        if ($itemCategory === null) {
            return [null, null];
        }

        $currentSub = $this->content($responses['itemSubcategories'])[0] ?? null;

        return [$itemCategory['category']['id'], $currentSub['subcategory']['id'] ?? null];
    }

    private function defaultSplitAllocations(ResourceType $resourceType): array
    {
        return DefaultSplitAllocation::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray();
    }

    private function defaultAllocationsFor(array $resources, bool $forceSplit, ResourceType $resourceType): ?array
    {
        if (! $forceSplit || old('allocations') !== null) {
            return null;
        }

        $validResourceIds = collect($resources)->pluck('id')->all();

        $filtered = collect($this->defaultSplitAllocations($resourceType))
            ->filter(fn ($allocation) => in_array($allocation['resource_id'], $validResourceIds, true))
            ->values()
            ->all();

        if (count($filtered) >= 2) {
            return $filtered;
        }

        if (count($resources) >= 2) {
            return [
                ['resource_id' => $resources[0]['id'], 'percentage' => 50],
                ['resource_id' => $resources[1]['id'], 'percentage' => 50],
            ];
        }

        return null;
    }
}
