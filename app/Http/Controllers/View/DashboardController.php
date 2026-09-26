<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\ReportingPeriod;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(): View
    {
        $resources = $this->api->resources();
        $children = $resources['status'] === 200 ? $resources['content'] : [];

        $periods = ReportingPeriod::query()->orderBy('sort_order')->get();

        $recentByChild = [];
        $periodTotalsByChild = [];

        foreach ($children as $child) {
            $items = $this->api->items($child['id'], ['sort' => 'effective_date:desc', 'limit' => 5, 'include-categories' => 'true', 'include-subcategories' => 'true']);
            $recentByChild[$child['id']] = $items['status'] === 200 ? $items['content'] : [];

            $periodTotalsByChild[$child['id']] = $periods->map(fn (ReportingPeriod $period) => $this->periodTotal($child['id'], $period))->all();
        }

        return view('dashboard.index', [
            'children' => $children,
            'recentByChild' => $recentByChild,
            'periodTotalsByChild' => $periodTotalsByChild,
            'apiError' => $resources['status'] !== 200,
        ]);
    }

    private function periodTotal(string $resourceId, ReportingPeriod $period): array
    {
        [$start, $end] = $period->currentWindow();

        $summary = $this->api->itemsSummary($resourceId, [
            'filter' => 'effective_date:'.$start->toDateString().':'.$end->toDateString(),
        ]);

        // One row per currency present in the range - almost always just
        // GBP, but a family could easily have the odd USD/EUR expense from
        // a trip, and those must never be silently added into GBP's total.
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
