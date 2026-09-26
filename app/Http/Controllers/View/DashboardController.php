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

        $items = $this->api->items($resourceId, [
            'collection' => 'true',
            'filter' => 'effective_date:'.$start->toDateString().':'.$end->toDateString(),
        ]);

        $total = 0.0;
        $currencyCode = null;

        if ($items['status'] === 200) {
            foreach ($items['content'] as $item) {
                $total += (float) $item['actualised_total'];
                $currencyCode ??= $item['currency']['code'];
            }
        }

        return [
            'name' => $period->name,
            'total' => number_format($total, 2, '.', ''),
            'currency' => $currencyCode ?? 'GBP',
        ];
    }
}
