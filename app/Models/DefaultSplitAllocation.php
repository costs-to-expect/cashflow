<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultSplitAllocation extends Model
{
    protected $fillable = [
        'resource_type_id',
        'resource_id',
        'percentage',
        'sort_order',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }
}
