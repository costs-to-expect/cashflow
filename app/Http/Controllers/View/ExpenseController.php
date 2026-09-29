<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function create(Request $request, ResourceType $resourceType): View
    {
        $resources = $this->resources();
        $currencies = $this->sortCurrenciesGbpFirst($this->currencies());
        $categories = $this->categories();

        return view('expenses.create', [
            'resources' => $resources,
            'currencies' => $currencies,
            'defaultCurrencyId' => $this->resolveDefaultCurrencyId($currencies),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories),
            'preselectedResourceId' => $request->query('resource_id'),
            'defaultAllocations' => $this->defaultAllocationsFor($resources, $request->boolean('split'), $resourceType),
            'defaultSplit' => $this->defaultSplitAllocations($resourceType),
            'nameSuggestions' => $this->nameSuggestions($resources),
        ]);
    }

    public function edit(ResourceType $resourceType, string $resource_id, string $item_id): View
    {
        $item = $this->api->item($resource_id, $item_id);

        abort_if($item['status'] !== 200, 404, 'That expense could not be found.');

        [$currentCategoryId, $currentSubcategoryId] = $this->currentCategorisation($resource_id, $item_id);

        $resources = $this->resources();
        $categories = $this->categories();

        return view('expenses.edit', [
            'resourceId' => $resource_id,
            'item' => $item['content'],
            'resources' => $resources,
            'currencies' => $this->sortCurrenciesGbpFirst($this->currencies()),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories),
            'currentCategoryId' => $currentCategoryId,
            'currentSubcategoryId' => $currentSubcategoryId,
            'nameSuggestions' => $this->nameSuggestions($resources),
        ]);
    }

    /**
     * Distinct names from recent expenses, for the "name" field's datalist.
     */
    private function nameSuggestions(array $resources): array
    {
        $names = [];

        foreach ($resources as $resource) {
            $items = $this->api->items($resource['id'], ['limit' => 100, 'sort' => 'effective_date:desc']);

            if ($items['status'] === 200) {
                foreach ($items['content'] as $item) {
                    $names[$item['name']] = true;
                }
            }
        }

        $names = array_keys($names);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return array_slice($names, 0, 150);
    }

    private function resources(): array
    {
        $resources = $this->api->resources();

        return $resources['status'] === 200 ? $resources['content'] : [];
    }

    private function currencies(): array
    {
        $currencies = $this->api->currencies();

        return $currencies['status'] === 200 ? $currencies['content'] : [];
    }

    private function categories(): array
    {
        $categories = $this->api->categories();

        return $categories['status'] === 200 ? $categories['content'] : [];
    }

    private function subcategoriesByCategory(array $categories): array
    {
        $map = [];

        foreach ($categories as $category) {
            $response = $this->api->subcategories($category['id']);

            $map[$category['id']] = $response['status'] === 200
                ? collect($response['content'])->map(fn ($s) => ['id' => $s['id'], 'name' => $s['name']])->all()
                : [];
        }

        return $map;
    }

    /**
     * @return array{0: ?string, 1: ?string} [currentCategoryId, currentSubcategoryId]
     */
    private function currentCategorisation(string $resourceId, string $itemId): array
    {
        $itemCategories = $this->api->itemCategories($resourceId, $itemId);
        $current = $itemCategories['status'] === 200 ? ($itemCategories['content'][0] ?? null) : null;

        if ($current === null) {
            return [null, null];
        }

        $currentCategoryId = $current['category']['id'];

        $itemSubcategories = $this->api->itemSubcategories($resourceId, $itemId, $current['id']);
        $currentSub = $itemSubcategories['status'] === 200 ? ($itemSubcategories['content'][0] ?? null) : null;

        return [$currentCategoryId, $currentSub['subcategory']['id'] ?? null];
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
