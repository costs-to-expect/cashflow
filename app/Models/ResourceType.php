<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceType extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'item_type',
        'api_resource_type_id',
        'api_item_type_id',
        'item_subtype_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
