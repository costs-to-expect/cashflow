<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\RecurringExpense;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class RecurringController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(): View
    {
        return view('recurring.index', [
            'recurringExpenses' => RecurringExpense::query()->with('allocations')->orderBy('name')->get(),
            'childrenById' => $this->childrenById(),
            'currenciesById' => collect($this->currencies())->keyBy('id')->all(),
        ]);
    }

    public function create(): View
    {
        $currencies = $this->sortCurrenciesGbpFirst($this->currencies());

        return view('recurring.create', [
            'children' => $this->children(),
            'currencies' => $currencies,
            'defaultCurrencyId' => $this->resolveDefaultCurrencyId($currencies),
            'defaultSplit' => DefaultSplitAllocation::query()->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }

    public function edit(RecurringExpense $recurringExpense): View
    {
        $recurringExpense->load('allocations');

        return view('recurring.edit', [
            'recurringExpense' => $recurringExpense,
            'children' => $this->children(),
            'currencies' => $this->sortCurrenciesGbpFirst($this->currencies()),
        ]);
    }

    private function children(): array
    {
        $resources = $this->api->resources();

        return $resources['status'] === 200 ? $resources['content'] : [];
    }

    private function childrenById(): array
    {
        return collect($this->children())->keyBy('id')->all();
    }

    private function currencies(): array
    {
        $currencies = $this->api->currencies();

        return $currencies['status'] === 200 ? $currencies['content'] : [];
    }
}
