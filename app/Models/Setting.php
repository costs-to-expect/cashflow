<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'resource_type_id',
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null, ?ResourceType $resourceType = null): ?string
    {
        return static::query()
            ->where('key', $key)
            ->where('resource_type_id', $resourceType?->id)
            ->value('value') ?? $default;
    }

    public static function set(string $key, string $value, ?ResourceType $resourceType = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key, 'resource_type_id' => $resourceType?->id],
            ['value' => $value],
        );
    }
}
