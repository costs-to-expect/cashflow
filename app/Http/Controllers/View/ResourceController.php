<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;
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
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $categoriesEnabled = $resourceType->categoriesEnabled();

        // Nothing here depends on anything else's response, so it all goes
        // in one pool - along with what the nav needs. A resource that
        // doesn't exist costs a few wasted requests, but it's only a 404.
        $responses = $this->api->pool(function (RequestPool $pool) use ($resourceType, $resource_id, $page, $perPage, $categoriesEnabled) {
            $pool->resource('resource', $resource_id);

            $pool->items('items', $resource_id, [
                'sort' => 'effective_date:desc',
                'limit' => $perPage,
                'offset' => ($page - 1) * $perPage,
                // The labels are only shown while categories are turned on.
                ...($categoriesEnabled ? ['include-categories' => 'true', 'include-subcategories' => 'true'] : []),
            ]);

            $this->periodTotals->poolResource($pool, $resourceType, $resource_id);

            $pool->navigation();
        });

        $resource = $responses['resource'];

        abort_if($resource['status'] !== 200, 404, 'That resource could not be found.');

        $items = $responses['items'];

        return view('resources.show', [
            'categoriesEnabled' => $categoriesEnabled,
            'resource' => $resource['content'],
            'periodTotals' => $this->periodTotals->forResource($resourceType, $resource_id, $responses),
            'items' => $items['status'] === 200 ? $items['content'] : [],
            'page' => $page,
            'hasMore' => $items['status'] === 200 && count($items['content']) === $perPage,
        ]);
    }
}
