<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'currency_id',
        'total',
        'frequency',
        'day_of_month',
        'next_run_date',
        'active',
    ];

    protected $casts = [
        'next_run_date' => 'date',
        'active' => 'boolean',
        'day_of_month' => 'integer',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(RecurringExpenseAllocation::class)->orderBy('sort_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(RecurringExpenseRun::class);
    }

    /**
     * Advance next_run_date by one month, clamped to the last day of the
     * target month when day_of_month doesn't exist there (e.g. 31st in Feb).
     */
    public function advanceNextRunDate(): void
    {
        $next = $this->next_run_date->copy()->addMonthNoOverflow()->startOfMonth();
        $daysInMonth = $next->daysInMonth;

        $this->next_run_date = $next->day(min($this->day_of_month, $daysInMonth));
        $this->save();
    }
}
