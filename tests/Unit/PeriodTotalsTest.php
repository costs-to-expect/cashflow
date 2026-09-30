<?php

namespace Tests\Unit;

use App\Service\Reporting\PeriodTotals;
use PHPUnit\Framework\TestCase;

class PeriodTotalsTest extends TestCase
{
    private function row(string $currency, float $amount): array
    {
        return ['currency' => $currency, 'total' => number_format($amount, 2), 'amount' => $amount];
    }

    public function test_share_is_the_whole_percent_of_the_combined_total(): void
    {
        $combined = [$this->row('GBP', 4298.95)];

        $this->assertSame(72, PeriodTotals::share([$this->row('GBP', 3092.55)], $combined));
        $this->assertSame(28, PeriodTotals::share([$this->row('GBP', 1206.40)], $combined));
    }

    public function test_share_is_zero_when_the_combined_total_is_zero_or_missing(): void
    {
        $this->assertSame(0, PeriodTotals::share([$this->row('GBP', 10.0)], [$this->row('GBP', 0.0)]));
        $this->assertSame(0, PeriodTotals::share([$this->row('GBP', 10.0)], []));
        $this->assertSame(0, PeriodTotals::share([], [$this->row('GBP', 10.0)]));
    }

    public function test_share_is_measured_in_the_combined_totals_leading_currency_only(): void
    {
        $combined = [$this->row('GBP', 200.0), $this->row('USD', 100.0)];

        // Only the GBP figure counts towards the share - the USD isn't added in.
        $this->assertSame(50, PeriodTotals::share([$this->row('GBP', 100.0), $this->row('USD', 100.0)], $combined));

        // Nothing in the leading currency means no share of it.
        $this->assertSame(0, PeriodTotals::share([$this->row('USD', 100.0)], $combined));
    }

    public function test_with_shares_matches_entries_up_by_key(): void
    {
        $combined = [
            ['key' => 'period-1', 'totals' => [$this->row('GBP', 100.0)]],
            ['key' => 'all-time', 'totals' => [$this->row('GBP', 400.0)]],
        ];

        // Deliberately in a different order to the combined entries.
        $entries = [
            ['key' => 'all-time', 'totals' => [$this->row('GBP', 100.0)]],
            ['key' => 'period-1', 'totals' => [$this->row('GBP', 25.0)]],
        ];

        $shares = array_column(PeriodTotals::withShares($entries, $combined), 'share', 'key');

        $this->assertSame(['all-time' => 25, 'period-1' => 25], $shares);
    }
}
