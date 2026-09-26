<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function create(): View
    {
        return view('expenses.create', [
            'children' => $this->children(),
            'currencies' => $this->currencies(),
            'defaultCurrencyId' => config('api.default_currency_id'),
        ]);
    }

    public function edit(string $resource_id, string $item_id): View
    {
        $item = $this->api->item($resource_id, $item_id);

        abort_if($item['status'] !== 200, 404, 'That expense could not be found.');

        return view('expenses.edit', [
            'resourceId' => $resource_id,
            'item' => $item['content'],
            'children' => $this->children(),
            'currencies' => $this->currencies(),
        ]);
    }

    private function children(): array
    {
        $resources = $this->api->resources();

        return $resources['status'] === 200 ? $resources['content'] : [];
    }

    private function currencies(): array
    {
        $currencies = $this->api->currencies();

        return $currencies['status'] === 200 ? $currencies['content'] : [];
    }
}
