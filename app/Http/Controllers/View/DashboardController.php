<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(ResourceType $resourceType): View
    {
        $response = $this->api->resources();
        $resources = $response['status'] === 200 ? $response['content'] : [];

        $periods = ReportingPeriod::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get();

        $recentByResource = [];
        $periodTotalsByResource = [];

        foreach ($resources as $resource) {
            $items = $this->api->items($resource['id'], ['sort' => 'effective_date:desc', 'limit' => 5, 'include-categories' => 'true', 'include-subcategories' => 'true']);
            $recentByResource[$resource['id']] = $items['status'] === 200 ? $items['content'] : [];

            $periodTotalsByResource[$resource['id']] = $periods->map(
                fn (ReportingPeriod $period) => $this->periodTotal($period, fn (array $query) => $this->api->itemsSummary($resource['id'], $query))
            )->all();
        }

        $overallPeriodTotals = $periods->map(
            fn (ReportingPeriod $period) => $this->periodTotal($period, fn (array $query) => $this->api->resourceTypeItemsSummary($query))
        )->all();

        return view('dashboard.index', [
            'resources' => $resources,
            'recentByResource' => $recentByResource,
            'periodTotalsByResource' => $periodTotalsByResource,
            'overallPeriodTotals' => $overallPeriodTotals,
            'apiError' => $response['status'] !== 200,
        ]);
    }

    /**
     * @param  callable(array<string, mixed>): array  $fetchSummary
     */
    private function periodTotal(ReportingPeriod $period, callable $fetchSummary): array
    {
        [$start, $end] = $period->currentWindow();

        $summary = $fetchSummary([
            'filter' => 'effective_date:'.$start->toDateString().':'.$end->toDateString(),
        ]);

        // One row per currency present in the range - almost always just
        // one currency, but a resource could easily have the odd expense in
        // another currency, and those must never be silently added together.
        $totals = $summary['status'] === 200
            ? collect($summary['content'])->map(fn (array $row) => [
                'currency' => $row['currency']['code'],
                'total' => $row['subtotal'],
            ])->all()
            : [];

        return [
            'name' => $period->name,
            'totals' => $totals,
            'starts_on' => $start,
            'ends_on' => $end,
        ];
    }
}
