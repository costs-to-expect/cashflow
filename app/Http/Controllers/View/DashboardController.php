<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
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
        $response = $this->api->resources();
        $resources = $response['status'] === 200 ? $response['content'] : [];

        $overallPeriodTotals = $this->periodTotals->forResourceType($resourceType);

        $categoriesEnabled = $resourceType->categoriesEnabled();

        $recentByResource = [];
        $periodTotalsByResource = [];

        foreach ($resources as $resource) {
            $items = $this->api->items($resource['id'], [
                'sort' => 'effective_date:desc',
                'limit' => 5,
                // The labels are only shown while categories are turned on.
                ...($categoriesEnabled ? ['include-categories' => 'true', 'include-subcategories' => 'true'] : []),
            ]);
            $recentByResource[$resource['id']] = $items['status'] === 200 ? $items['content'] : [];

            $periodTotalsByResource[$resource['id']] = PeriodTotals::withShares(
                $this->periodTotals->forResource($resourceType, $resource['id']),
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
