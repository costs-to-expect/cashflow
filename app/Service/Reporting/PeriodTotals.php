<?php

declare(strict_types=1);

namespace App\Service\Reporting;

use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Support\Collection;

/**
 * Builds the totals shown for a resource type's reporting periods - the
 * current window of each configured period, plus a trailing "all time" entry
 * - either combined across every resource or for a single one.
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

    /** @var array<int, Collection<int, ReportingPeriod>> */
    private array $periodsByResourceType = [];

    public function __construct(private readonly ApiService $api) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function forResourceType(ResourceType $resourceType): array
    {
        return $this->build($resourceType, fn (array $query) => $this->api->resourceTypeItemsSummary($query));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forResource(ResourceType $resourceType, string $resourceId): array
    {
        return $this->build($resourceType, fn (array $query) => $this->api->itemsSummary($resourceId, $query));
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
     * @param  callable(array<string, mixed>): array  $fetchSummary
     * @return list<array<string, mixed>>
     */
    private function build(ResourceType $resourceType, callable $fetchSummary): array
    {
        $entries = $this->periods($resourceType)->map(function (ReportingPeriod $period) use ($fetchSummary) {
            [$start, $end] = $period->currentWindow();

            $summary = $fetchSummary([
                'filter' => 'effective_date:'.$start->toDateString().':'.$end->toDateString(),
            ]);

            return [
                'key' => 'period-'.$period->id,
                'name' => $period->name,
                'totals' => $this->currencyTotals($summary),
                'starts_on' => $start,
                'ends_on' => $end,
                'all_time' => false,
            ];
        })->all();

        // The summary endpoints return an all-time total when called with no
        // filter at all, so unlike the periods there's no date range to build.
        $entries[] = [
            'key' => self::ALL_TIME,
            'name' => 'All time',
            'totals' => $this->currencyTotals($fetchSummary([])),
            'starts_on' => null,
            'ends_on' => null,
            'all_time' => true,
        ];

        return $entries;
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

    /**
     * @return Collection<int, ReportingPeriod>
     */
    private function periods(ResourceType $resourceType): Collection
    {
        return $this->periodsByResourceType[$resourceType->id] ??= ReportingPeriod::query()
            ->where('resource_type_id', $resourceType->id)
            ->orderBy('sort_order')
            ->get();
    }
}
