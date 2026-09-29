<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Actions\ResourceType\VisibleResourceTypes;
use App\Http\Controllers\Controller;
use App\Service\Api\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceTypeController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    /**
     * Zero resource types - straight to create. Exactly one - straight to
     * its dashboard, so a single-resource-type install (today's norm) has
     * zero extra clicks. Two or more - a picker.
     */
    public function index(VisibleResourceTypes $visibleResourceTypes): View|RedirectResponse
    {
        $resourceTypes = $visibleResourceTypes();

        if ($resourceTypes->isEmpty()) {
            return redirect()->route('resource-types.create');
        }

        if ($resourceTypes->count() === 1) {
            return redirect()->route('dashboard', $resourceTypes->first());
        }

        return view('resource-types.index', [
            'resourceTypes' => $resourceTypes,
        ]);
    }

    public function create(): View
    {
        $response = $this->api->itemTypes();
        $itemTypes = $response['status'] === 200 ? $response['content'] : [];

        return view('resource-types.create', [
            'itemTypeOptions' => collect($itemTypes)
                ->whereIn('name', ['allocated-expense', 'allocated-transaction'])
                ->mapWithKeys(fn (array $itemType) => [$itemType['name'] => match ($itemType['name']) {
                    'allocated-expense' => 'Expense tracking',
                    'allocated-transaction' => 'Transaction tracking',
                }])
                ->all(),
        ]);
    }
}
