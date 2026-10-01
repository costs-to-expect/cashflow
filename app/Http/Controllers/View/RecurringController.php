<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\RecurringExpense;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;
use Illuminate\View\View;

class RecurringController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(ResourceType $resourceType): View
    {
        $responses = $this->api->pool(fn (RequestPool $pool) => $pool->navigation()->currencies());

        return view('recurring.index', [
            'recurringExpenses' => RecurringExpense::query()->where('resource_type_id', $resourceType->id)->with('allocations')->orderBy('name')->get(),
            'resourcesById' => collect($this->content($responses[RequestPool::RESOURCES]))->keyBy('id')->all(),
            'currenciesById' => collect($this->content($responses[RequestPool::CURRENCIES]))->keyBy('id')->all(),
        ]);
    }

    public function create(ResourceType $resourceType): View
    {
        [$first, $second, $categories] = $this->formOptions($resourceType);

        $currencies = $this->sortCurrenciesGbpFirst($this->content($first[RequestPool::CURRENCIES]));

        return view('recurring.create', [
            'resources' => $this->content($first[RequestPool::RESOURCES]),
            'currencies' => $currencies,
            'defaultCurrencyId' => $this->resolveDefaultCurrencyId($currencies),
            'categoriesEnabled' => $resourceType->categoriesEnabled(),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories, $second),
            'defaultSplit' => DefaultSplitAllocation::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }

    public function edit(ResourceType $resourceType, RecurringExpense $recurringExpense): View
    {
        abort_unless($recurringExpense->resource_type_id === $resourceType->id, 404);

        $recurringExpense->load('allocations');

        [$first, $second, $categories] = $this->formOptions($resourceType);

        return view('recurring.edit', [
            'recurringExpense' => $recurringExpense,
            'resources' => $this->content($first[RequestPool::RESOURCES]),
            'currencies' => $this->sortCurrenciesGbpFirst($this->content($first[RequestPool::CURRENCIES])),
            'categoriesEnabled' => $resourceType->categoriesEnabled(),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories, $second),
        ]);
    }

    /**
     * Everything the create and edit forms read from the API, in two waves
     * (the second needing the categories the first returns): the resources,
     * currencies and categories (and the nav's needs), then each category's
     * subcategories.
     *
     * @return array{0: array<string, array>, 1: array<string, array>, 2: array<int, array<string, mixed>>} the first wave's responses, the second's, and the categories
     */
    private function formOptions(ResourceType $resourceType): array
    {
        $first = $this->api->pool(fn (RequestPool $pool) => $this->poolFormOptions($pool, $resourceType));

        $categories = $this->content($first[RequestPool::CATEGORIES] ?? null);

        $second = $this->api->pool(fn (RequestPool $pool) => $this->poolSubcategories($pool, $categories));

        return [$first, $second, $categories];
    }
}
