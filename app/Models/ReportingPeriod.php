<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ReportingPeriod extends Model
{
    protected $fillable = [
        'resource_type_id',
        'name',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
        'sort_order',
    ];

    protected $casts = [
        'start_month' => 'integer',
        'start_day' => 'integer',
        'end_month' => 'integer',
        'end_day' => 'integer',
        'sort_order' => 'integer',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    /**
     * The absolute start/end dates of the current instance of this
     * recurring window, anchored to today - e.g. a "5 Apr -> 4 Apr"
     * financial year resolves to whichever such window today falls in.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function currentWindow(): array
    {
        $today = Carbon::today();

        $start = $this->dateFor($today->year, $this->start_month, $this->start_day);

        if ($start->gt($today)) {
            $start = $this->dateFor($today->year - 1, $this->start_month, $this->start_day);
        }

        $end = $this->dateFor($start->year, $this->end_month, $this->end_day);

        if ($end->lt($start)) {
            $end = $this->dateFor($start->year + 1, $this->end_month, $this->end_day);
        }

        return [$start, $end];
    }

    private function dateFor(int $year, int $month, int $day): Carbon
    {
        $date = Carbon::create($year, $month, 1)->startOfDay();

        return $date->day(min($day, $date->daysInMonth));
    }
}
