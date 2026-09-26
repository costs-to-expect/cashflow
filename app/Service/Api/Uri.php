<?php

declare(strict_types=1);

namespace App\Service\Api;

use Illuminate\Support\Facades\Config;

/**
 * Builds the URI paths for the Costs to Expect API endpoints this app uses.
 * Every method returns a path relative to the API base url, the fixed
 * "kids" resource type id is baked in rather than passed around everywhere.
 */
class Uri
{
    private const VERSION = 'v3';

    public static function signIn(): string
    {
        return '/'.self::VERSION.'/auth/login';
    }

    public static function authUser(): string
    {
        return '/'.self::VERSION.'/auth/user';
    }

    public static function currencies(): string
    {
        return '/'.self::VERSION.'/currencies?collection=true';
    }

    public static function resources(): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/resources?collection=true';
    }

    public static function resource(string $resourceId): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/resources/'.$resourceId;
    }

    public static function items(string $resourceId, array $query = []): string
    {
        $uri = '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/resources/'.$resourceId.'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    public static function item(string $resourceId, string $itemId): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/resources/'.$resourceId.'/items/'.$itemId;
    }

    public static function categories(): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/categories?collection=true';
    }

    public static function subcategories(string $categoryId): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/categories/'.$categoryId.'/subcategories?collection=true';
    }

    public static function itemCategories(string $resourceId, string $itemId): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/resources/'.$resourceId.'/items/'.$itemId.'/categories';
    }

    public static function itemCategory(string $resourceId, string $itemId, string $itemCategoryId): string
    {
        return self::itemCategories($resourceId, $itemId).'/'.$itemCategoryId;
    }

    public static function itemSubcategories(string $resourceId, string $itemId, string $itemCategoryId): string
    {
        return self::itemCategory($resourceId, $itemId, $itemCategoryId).'/subcategories';
    }

    public static function itemSubcategory(string $resourceId, string $itemId, string $itemCategoryId, string $itemSubcategoryId): string
    {
        return self::itemSubcategories($resourceId, $itemId, $itemCategoryId).'/'.$itemSubcategoryId;
    }

    private static function resourceTypeId(): string
    {
        return (string) Config::get('api.resource_type_id');
    }
}
