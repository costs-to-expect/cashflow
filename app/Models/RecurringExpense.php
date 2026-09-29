<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_type_id',
        'name',
        'description',
        'currency_id',
        'total',
        'category_id',
        'subcategory_id',
        'frequency',
        'day_of_month',
        'starts_on',
        'ends_on',
        'next_run_date',
        'active',
    ];

    protected $casts = [
        'next_run_date' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'active' => 'boolean',
        'day_of_month' => 'integer',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

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
