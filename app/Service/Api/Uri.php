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

    /**
     * Server-side aggregation (count/subtotal per currency) rather than the
     * plain items collection - the API's "collection=true" override does
     * not apply to the items endpoint, so summing a date range client-side
     * would otherwise mean paging through every matching item.
     *
     * Known issue (API ticket filed): the filtered form of this endpoint
     * returns an empty result for items created moments earlier on a brand
     * new resource - suspected to be a scheduler/cache-population job not
     * running locally. Works correctly on established data.
     */
    public static function itemsSummary(string $resourceId, array $query = []): string
    {
        $uri = '/'.self::VERSION.'/summary/resource-types/'.self::resourceTypeId().'/resources/'.$resourceId.'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    /**
     * Same aggregation as itemsSummary(), but across every resource under
     * the resource type at once (e.g. every child's expenses combined) -
     * for a resource-type-wide total rather than one child's.
     */
    public static function resourceTypeItemsSummary(array $query = []): string
    {
        $uri = '/'.self::VERSION.'/summary/resource-types/'.self::resourceTypeId().'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    public static function categories(): string
    {
        return self::categoriesBase().'?collection=true';
    }

    public static function category(string $categoryId): string
    {
        return self::categoriesBase().'/'.$categoryId;
    }

    public static function subcategories(string $categoryId): string
    {
        return self::subcategoriesBase($categoryId).'?collection=true';
    }

    public static function subcategory(string $categoryId, string $subcategoryId): string
    {
        return self::subcategoriesBase($categoryId).'/'.$subcategoryId;
    }

    private static function categoriesBase(): string
    {
        return '/'.self::VERSION.'/resource-types/'.self::resourceTypeId().'/categories';
    }

    private static function subcategoriesBase(string $categoryId): string
    {
        return self::category($categoryId).'/subcategories';
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
        return (string) Config::get('app.api.resource_type_id');
    }
}
