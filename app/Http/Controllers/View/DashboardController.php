<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;
use App\Service\Reporting\PeriodTotals;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ApiService $api,
        private readonly PeriodTotals $periodTotals,
    ) {}

    public function index(ResourceType $resourceType): View
    {
        $categoriesEnabled = $resourceType->categoriesEnabled();

        // Two waves, the second needing what the first returns. The first is
        // everything that doesn't need a resource's id: the resources
        // themselves, the combined totals and what the nav needs.
        $first = $this->api->pool(function (RequestPool $pool) use ($resourceType) {
            $pool->navigation();
            $this->periodTotals->poolResourceType($pool, $resourceType);
        });

        $response = $first[RequestPool::RESOURCES];
        $resources = $response['status'] === 200 ? $response['content'] : [];

        $overallPeriodTotals = $this->periodTotals->forResourceType($resourceType, $first);

        // The second is each resource's recent items and own totals.
        $second = $this->api->pool(function (RequestPool $pool) use ($resourceType, $resources, $categoriesEnabled) {
            foreach ($resources as $resource) {
                $pool->items('items.'.$resource['id'], $resource['id'], [
                    'sort' => 'effective_date:desc',
                    'limit' => 5,
                    // The labels are only shown while categories are turned on.
                    ...($categoriesEnabled ? ['include-categories' => 'true', 'include-subcategories' => 'true'] : []),
                ]);

                $this->periodTotals->poolResource($pool, $resourceType, $resource['id']);
            }
        });

        $recentByResource = [];
        $periodTotalsByResource = [];

        foreach ($resources as $resource) {
            $items = $second['items.'.$resource['id']];
            $recentByResource[$resource['id']] = $items['status'] === 200 ? $items['content'] : [];

            $periodTotalsByResource[$resource['id']] = PeriodTotals::withShares(
                $this->periodTotals->forResource($resourceType, $resource['id'], $second),
                $overallPeriodTotals,
            );
        }

        return view('dashboard.index', [
            'resources' => $resources,
            'recentByResource' => $recentByResource,
            'periodTotalsByResource' => $periodTotalsByResource,
            'overallPeriodTotals' => $overallPeriodTotals,
            'categoriesEnabled' => $categoriesEnabled,
            'apiError' => $response['status'] !== 200,
        ]);
    }
}
