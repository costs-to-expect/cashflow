<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\RecurringExpense;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class RecurringController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(ResourceType $resourceType): View
    {
        return view('recurring.index', [
            'recurringExpenses' => RecurringExpense::query()->where('resource_type_id', $resourceType->id)->with('allocations')->orderBy('name')->get(),
            'resourcesById' => $this->resourcesById(),
            'currenciesById' => collect($this->currencies())->keyBy('id')->all(),
        ]);
    }

    public function create(ResourceType $resourceType): View
    {
        $currencies = $this->sortCurrenciesGbpFirst($this->currencies());
        $categories = $this->categories();

        return view('recurring.create', [
            'resources' => $this->resources(),
            'currencies' => $currencies,
            'defaultCurrencyId' => $this->resolveDefaultCurrencyId($currencies),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories),
            'defaultSplit' => DefaultSplitAllocation::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }

    public function edit(ResourceType $resourceType, RecurringExpense $recurringExpense): View
    {
        abort_unless($recurringExpense->resource_type_id === $resourceType->id, 404);

        $recurringExpense->load('allocations');
        $categories = $this->categories();

        return view('recurring.edit', [
            'recurringExpense' => $recurringExpense,
            'resources' => $this->resources(),
            'currencies' => $this->sortCurrenciesGbpFirst($this->currencies()),
            'categories' => $categories,
            'subcategoriesByCategory' => $this->subcategoriesByCategory($categories),
        ]);
    }

    private function resources(): array
    {
        $resources = $this->api->resources();

        return $resources['status'] === 200 ? $resources['content'] : [];
    }

    private function resourcesById(): array
    {
        return collect($this->resources())->keyBy('id')->all();
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
}
