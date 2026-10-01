<?php

declare(strict_types=1);

namespace App\Service\Api;

/**
 * Builds the URI paths for the Costs to Expect API endpoints this app uses.
 * Every path scoped to a resource type takes its id explicitly - the app
 * can have several resource types active at once, so nothing here reads a
 * "the" resource type from global config any more.
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

    public static function resourceTypes(): string
    {
        return '/'.self::VERSION.'/resource-types';
    }

    public static function permittedResourceTypes(): string
    {
        return '/'.self::VERSION.'/auth/user/permitted-resource-types';
    }

    public static function itemTypes(): string
    {
        return '/'.self::VERSION.'/item-types';
    }

    public static function itemSubtypes(string $itemTypeId): string
    {
        return '/'.self::VERSION.'/item-types/'.$itemTypeId.'/item-subtypes';
    }

    public static function resources(string $resourceTypeId): string
    {
        return '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/resources?collection=true';
    }

    public static function resource(string $resourceTypeId, string $resourceId): string
    {
        return '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/resources/'.$resourceId;
    }

    public static function items(string $resourceTypeId, string $resourceId, array $query = []): string
    {
        $uri = '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/resources/'.$resourceId.'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    public static function item(string $resourceTypeId, string $resourceId, string $itemId): string
    {
        return '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/resources/'.$resourceId.'/items/'.$itemId;
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
    public static function itemsSummary(string $resourceTypeId, string $resourceId, array $query = []): string
    {
        $uri = '/'.self::VERSION.'/summary/resource-types/'.$resourceTypeId.'/resources/'.$resourceId.'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    /**
     * Same aggregation as itemsSummary(), but across every resource under
     * the resource type at once (e.g. every resource's expenses combined) -
     * for a resource-type-wide total rather than one resource's.
     */
    public static function resourceTypeItemsSummary(string $resourceTypeId, array $query = []): string
    {
        $uri = '/'.self::VERSION.'/summary/resource-types/'.$resourceTypeId.'/items';

        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        return $uri;
    }

    public static function categories(string $resourceTypeId): string
    {
        return self::categoriesBase($resourceTypeId).'?collection=true';
    }

    public static function category(string $resourceTypeId, string $categoryId): string
    {
        return self::categoriesBase($resourceTypeId).'/'.$categoryId;
    }

    public static function subcategories(string $resourceTypeId, string $categoryId): string
    {
        return self::subcategoriesBase($resourceTypeId, $categoryId).'?collection=true';
    }

    public static function subcategory(string $resourceTypeId, string $categoryId, string $subcategoryId): string
    {
        return self::subcategoriesBase($resourceTypeId, $categoryId).'/'.$subcategoryId;
    }

    private static function categoriesBase(string $resourceTypeId): string
    {
        return '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/categories';
    }

    private static function subcategoriesBase(string $resourceTypeId, string $categoryId): string
    {
        return self::category($resourceTypeId, $categoryId).'/subcategories';
    }

    public static function itemCategories(string $resourceTypeId, string $resourceId, string $itemId): string
    {
        return '/'.self::VERSION.'/resource-types/'.$resourceTypeId.'/resources/'.$resourceId.'/items/'.$itemId.'/categories';
    }

    public static function itemCategory(string $resourceTypeId, string $resourceId, string $itemId, string $itemCategoryId): string
    {
        return self::itemCategories($resourceTypeId, $resourceId, $itemId).'/'.$itemCategoryId;
    }

    public static function itemSubcategories(string $resourceTypeId, string $resourceId, string $itemId, string $itemCategoryId): string
    {
        return self::itemCategory($resourceTypeId, $resourceId, $itemId, $itemCategoryId).'/subcategories';
    }

    public static function itemSubcategory(string $resourceTypeId, string $resourceId, string $itemId, string $itemCategoryId, string $itemSubcategoryId): string
    {
        return self::itemSubcategories($resourceTypeId, $resourceId, $itemId, $itemCategoryId).'/'.$itemSubcategoryId;
    }
}
