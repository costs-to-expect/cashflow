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

    /**
     * URLs show the API's own resource type id rather than this app's
     * internal auto-increment one.
     */
    public function getRouteKeyName(): string
    {
        return 'api_resource_type_id';
    }

    /**
     * Whether expenses in this resource type are tagged with a category and
     * subcategory. On unless explicitly turned off, so resource types that
     * existed before the setting keep the fields they always had.
     */
    public function categoriesEnabled(): bool
    {
        return Setting::get('categories_enabled', '1', $this) === '1';
    }

    public function setCategoriesEnabled(bool $enabled): void
    {
        Setting::set('categories_enabled', $enabled ? '1' : '0', $this);
    }

    public function itemTypeLabel(): string
    {
        return match ($this->item_type) {
            'allocated-expense' => 'Expense tracking',
            'allocated-transaction' => 'Transaction tracking',
            default => $this->item_type,
        };
    }
}
