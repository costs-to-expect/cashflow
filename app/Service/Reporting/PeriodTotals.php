<?php

declare(strict_types=1);

namespace App\Service\Reporting;

use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Service\Api\RequestPool;
use Illuminate\Support\Carbon;

/**
 * Builds the totals shown for a resource type's reporting periods - the
 * current window of each configured period, plus a trailing "all time" entry
 * - either combined across every resource or for a single one.
 *
 * The summaries behind them are fetched in a pool (see ApiService::pool()),
 * in two steps so a page can pool them along with its own requests: the
 * poolResourceType() / poolResource() methods add the requests to its
 * RequestPool, then forResourceType() / forResource() turn the pool's
 * responses into the entries.
 *
 * Every entry looks like:
 *
 *   key       stable id (period-<id> or "all-time"), used to link the same
 *             period up across the page and remembered by the switcher
 *   name      display name
 *   totals    one row per currency: currency, total (formatted), amount (float)
 *   starts_on / ends_on   Carbon dates, null for the all-time entry
 *   all_time  whether this is the all-time entry
 */
class PeriodTotals
{
    public const ALL_TIME = 'all-time';

    /**
     * Stands in for a resource's id in the key of a combined entry's
     * request, see key().
     */
    private const RESOURCE_TYPE = 'resource-type';

    /**
     * @var array<int, list<array{key: string, name: string, starts_on: ?Carbon, ends_on: ?Carbon, all_time: bool, query: array<string, string>}>>
     */
    private array $windowsByResourceType = [];

    /**
     * Adds the requests forResourceType() needs to the pool.
     */
    public function poolResourceType(RequestPool $pool, ResourceType $resourceType): void
    {
        foreach ($this->windows($resourceType) as $window) {
            $pool->resourceTypeItemsSummary(self::key(self::RESOURCE_TYPE, $window['key']), $window['query']);
        }
    }

    /**
     * Adds the requests forResource() needs to the pool.
     */
    public function poolResource(RequestPool $pool, ResourceType $resourceType, string $resourceId): void
    {
        foreach ($this->windows($resourceType) as $window) {
            $pool->itemsSummary(self::key($resourceId, $window['key']), $resourceId, $window['query']);
        }
    }

    /**
     * @param  array<string, array>  $responses  the pool's, after poolResourceType() added to it
     * @return list<array<string, mixed>>
     */
    public function forResourceType(ResourceType $resourceType, array $responses): array
    {
        return $this->build($resourceType, self::RESOURCE_TYPE, $responses);
    }

    /**
     * @param  array<string, array>  $responses  the pool's, after poolResource() added to it
     * @return list<array<string, mixed>>
     */
    public function forResource(ResourceType $resourceType, string $resourceId, array $responses): array
    {
        return $this->build($resourceType, $resourceId, $responses);
    }

    /**
     * Adds a "share" to each of a resource's entries - its whole-percent
     * portion of the matching combined entry, i.e. how much of the
     * resource type's total that resource accounts for.
     *
     * @param  list<array<string, mixed>>  $entries  one resource's entries
     * @param  list<array<string, mixed>>  $combined  the resource type's combined entries
     * @return list<array<string, mixed>>
     */
    public static function withShares(array $entries, array $combined): array
    {
        $combinedByKey = [];

        foreach ($combined as $entry) {
            $combinedByKey[$entry['key']] = $entry['totals'];
        }

        foreach ($entries as $index => $entry) {
            $entries[$index]['share'] = self::share($entry['totals'], $combinedByKey[$entry['key']] ?? []);
        }

        return $entries;
    }

    /**
     * Shares only make sense within one currency, so a resource is measured
     * against the combined total's leading currency (its first row - the
     * one shown as the headline amount). A resource with nothing in that
     * currency, or a combined total of zero, has a share of 0.
     *
     * @param  list<array{currency: string, total: string, amount: float}>  $totals
     * @param  list<array{currency: string, total: string, amount: float}>  $combinedTotals
     */
    public static function share(array $totals, array $combinedTotals): int
    {
        $lead = $combinedTotals[0] ?? null;

        if ($lead === null || $lead['amount'] <= 0) {
            return 0;
        }

        foreach ($totals as $row) {
            if ($row['currency'] === $lead['currency']) {
                return (int) round($row['amount'] / $lead['amount'] * 100);
            }
        }

        return 0;
    }

    /**
     * @param  array<string, array>  $responses
     * @return list<array<string, mixed>>
     */
    private function build(ResourceType $resourceType, string $scope, array $responses): array
    {
        return array_map(fn (array $window) => [
            'key' => $window['key'],
            'name' => $window['name'],
            'totals' => $this->currencyTotals($responses[self::key($scope, $window['key'])]),
            'starts_on' => $window['starts_on'],
            'ends_on' => $window['ends_on'],
            'all_time' => $window['all_time'],
        ], $this->windows($resourceType));
    }

    /**
     * Where a pooled summary request's response is filed: the combined
     * entries (or a resource's) for one window.
     */
    private static function key(string $scope, string $windowKey): string
    {
        return 'summary.'.$scope.'.'.$windowKey;
    }

    /**
     * The window of each of the resource type's periods as the current one
     * stands today, then the trailing all-time one, with the summary query
     * for each. Worked out once, so what's requested and what's reported
     * can't drift apart (e.g. across midnight).
     *
     * @return list<array{key: string, name: string, starts_on: ?Carbon, ends_on: ?Carbon, all_time: bool, query: array<string, string>}>
     */
    private function windows(ResourceType $resourceType): array
    {
        return $this->windowsByResourceType[$resourceType->id] ??= [
            ...ReportingPeriod::query()
                ->where('resource_type_id', $resourceType->id)
                ->orderBy('sort_order')
                ->get()
                ->map(function (ReportingPeriod $period) {
                    [$start, $end] = $period->currentWindow();

                    return [
                        'key' => 'period-'.$period->id,
                        'name' => $period->name,
                        'starts_on' => $start,
                        'ends_on' => $end,
                        'all_time' => false,
                        'query' => ['filter' => 'effective_date:'.$start->toDateString().':'.$end->toDateString()],
                    ];
                })
                ->all(),

            // The summary endpoints return an all-time total when called
            // with no filter at all, so unlike the periods there's no date
            // range to build.
            [
                'key' => self::ALL_TIME,
                'name' => 'All time',
                'starts_on' => null,
                'ends_on' => null,
                'all_time' => true,
                'query' => [],
            ],
        ];
    }

    /**
     * One row per currency in the summary - almost always just one
     * currency, but a resource could easily have the odd expense in
     * another currency, and those must never be silently added together.
     *
     * @return list<array{currency: string, total: string, amount: float}>
     */
    private function currencyTotals(array $summary): array
    {
        if ($summary['status'] !== 200) {
            return [];
        }

        return collect($summary['content'])->map(fn (array $row) => [
            'currency' => $row['currency']['code'],
            'total' => number_format((float) $row['subtotal'], 2),
            'amount' => (float) $row['subtotal'],
        ])->values()->all();
    }
}
