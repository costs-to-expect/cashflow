<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Reporting\PeriodTotals;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function __construct(
        private readonly ApiService $api,
        private readonly PeriodTotals $periodTotals,
    ) {}

    /**
     * $resourceType isn't used directly here, but a route-bound model
     * parameter must be declared on every {resourceType}-prefixed route's
     * controller method - that's what tells Laravel's implicit route
     * binding to actually substitute it into a real model, which the shared
     * layout (nav links, current-resource-type-scoped settings lookups)
     * depends on for every page.
     */
    public function create(ResourceType $resourceType): View
    {
        return view('resources.create');
    }

    public function show(Request $request, ResourceType $resourceType, string $resource_id): View
    {
        $resource = $this->api->resource($resource_id);

        abort_if($resource['status'] !== 200, 404, 'That resource could not be found.');

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $categoriesEnabled = $resourceType->categoriesEnabled();

        $items = $this->api->items($resource_id, [
            'sort' => 'effective_date:desc',
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            // The labels are only shown while categories are turned on.
            ...($categoriesEnabled ? ['include-categories' => 'true', 'include-subcategories' => 'true'] : []),
        ]);

        return view('resources.show', [
            'categoriesEnabled' => $categoriesEnabled,
            'resource' => $resource['content'],
            'periodTotals' => $this->periodTotals->forResource($resourceType, $resource_id),
            'items' =>$items['status'] === 200 ? $items['content'] : [],
            'page' => $page,
            'hasMore' => $items['status'] === 200 && count($items['content']) === $perPage,
        ]);
    }
}
