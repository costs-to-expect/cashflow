<?php

namespace Tests\Feature;

use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Service\Api\RequestPool;
use App\Service\Reporting\PeriodTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PeriodTotalsPoolingTest extends TestCase
{
    use RefreshDatabase;

    private ResourceType $resourceType;

    private ReportingPeriod $taxYear;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-30');

        $this->resourceType = ResourceType::create([
            'user_id' => 'u-1',
            'name' => 'Kids',
            'description' => 'Kids',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => 'rt-1',
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);

        $this->taxYear = ReportingPeriod::create(['resource_type_id' => $this->resourceType->id, 'name' => 'Tax year', 'start_month' => 4, 'start_day' => 6, 'end_month' => 4, 'end_day' => 5, 'sort_order' => 1]);
    }

    private function response(array $rows, int $status = 200): array
    {
        return ['status' => $status, 'content' => $rows, 'fields' => []];
    }

    private function gbp(string $subtotal): array
    {
        return ['currency' => ['code' => 'GBP'], 'subtotal' => $subtotal];
    }

    public function test_a_resources_requests_are_one_per_period_then_all_time(): void
    {
        $pool = new RequestPool('rt-1');

        (new PeriodTotals)->poolResource($pool, $this->resourceType, 'r-1');

        $requests = $pool->requests();

        $this->assertSame(['summary.r-1.period-'.$this->taxYear->id, 'summary.r-1.all-time'], array_keys($requests));
        $this->assertSame('/v3/summary/resource-types/rt-1/resources/r-1/items?filter=effective_date%3A2026-04-06%3A2027-04-05', $requests['summary.r-1.period-'.$this->taxYear->id]->uri);
        $this->assertSame('/v3/summary/resource-types/rt-1/resources/r-1/items', $requests['summary.r-1.all-time']->uri);
    }

    public function test_the_combined_requests_are_keyed_apart_from_a_resources(): void
    {
        $pool = new RequestPool('rt-1');
        $totals = new PeriodTotals;

        $totals->poolResourceType($pool, $this->resourceType);
        $totals->poolResource($pool, $this->resourceType, 'r-1');

        $this->assertSame([
            'summary.resource-type.period-'.$this->taxYear->id,
            'summary.resource-type.all-time',
            'summary.r-1.period-'.$this->taxYear->id,
            'summary.r-1.all-time',
        ], array_keys($pool->requests()));

        $this->assertSame('/v3/summary/resource-types/rt-1/items', $pool->requests()['summary.resource-type.all-time']->uri);
    }

    public function test_entries_are_built_from_the_responses_filed_under_their_keys(): void
    {
        $period = 'period-'.$this->taxYear->id;

        $entries = (new PeriodTotals)->forResource($this->resourceType, 'r-1', [
            "summary.r-1.{$period}" => $this->response([$this->gbp('12.5')]),
            'summary.r-1.all-time' => $this->response([$this->gbp('1234.5'), ['currency' => ['code' => 'USD'], 'subtotal' => '3']]),
            // Another scope's, which must not be picked up.
            "summary.resource-type.{$period}" => $this->response([$this->gbp('999')]),
        ]);

        $this->assertCount(2, $entries);

        $this->assertSame($period, $entries[0]['key']);
        $this->assertSame('Tax year', $entries[0]['name']);
        $this->assertSame([['currency' => 'GBP', 'total' => '12.50', 'amount' => 12.5]], $entries[0]['totals']);
        $this->assertSame('2026-04-06', $entries[0]['starts_on']->toDateString());
        $this->assertSame('2027-04-05', $entries[0]['ends_on']->toDateString());
        $this->assertFalse($entries[0]['all_time']);

        $this->assertSame(PeriodTotals::ALL_TIME, $entries[1]['key']);
        $this->assertSame('All time', $entries[1]['name']);
        $this->assertSame(['GBP', 'USD'], array_column($entries[1]['totals'], 'currency'));
        $this->assertSame('1,234.50', $entries[1]['totals'][0]['total']);
        $this->assertNull($entries[1]['starts_on']);
        $this->assertTrue($entries[1]['all_time']);
    }

    public function test_a_summary_the_api_could_not_give_has_no_totals_rather_than_failing_the_page(): void
    {
        $period = 'period-'.$this->taxYear->id;

        $entries = (new PeriodTotals)->forResourceType($this->resourceType, [
            "summary.resource-type.{$period}" => $this->response([], 500),
            'summary.resource-type.all-time' => $this->response([$this->gbp('10')]),
        ]);

        $this->assertSame([], $entries[0]['totals']);
        $this->assertSame('10.00', $entries[1]['totals'][0]['total']);
    }

    public function test_a_resource_type_with_no_periods_still_has_the_all_time_entry(): void
    {
        $this->taxYear->delete();

        $pool = new RequestPool('rt-1');

        (new PeriodTotals)->poolResourceType($pool, $this->resourceType);

        $this->assertSame(['summary.resource-type.all-time'], array_keys($pool->requests()));
    }
}
