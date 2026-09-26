<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultSplitAllocation extends Model
{
    protected $fillable = [
        'resource_id',
        'percentage',
        'sort_order',
    ];
}
