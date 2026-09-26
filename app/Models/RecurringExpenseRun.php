<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringExpenseRun extends Model
{
    protected $fillable = [
        'recurring_expense_id',
        'run_date',
        'created_item_ids',
    ];

    protected $casts = [
        'run_date' => 'date',
        'created_item_ids' => 'array',
    ];

    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(RecurringExpense::class);
    }
}
