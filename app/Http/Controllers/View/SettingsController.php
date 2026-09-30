<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Models\Setting;
use App\Service\Api\ApiService;
use App\Service\Api\RequestPool;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(ResourceType $resourceType): View
    {
        return view('settings.index', [
            'categoriesEnabled' => $resourceType->categoriesEnabled(),
        ]);
    }

    public function useCategories(ResourceType $resourceType): View
    {
        return view('settings.use-categories', [
            'enabled' => $resourceType->categoriesEnabled(),
        ]);
    }

    public function defaultSplit(ResourceType $resourceType): View
    {
        $responses = $this->api->pool(fn (RequestPool $pool) => $pool->navigation());

        return view('settings.default-split', [
            'resources' => $this->content($responses[RequestPool::RESOURCES]),
            'allocations' => DefaultSplitAllocation::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }

    public function resourceNaming(ResourceType $resourceType): View
    {
        return view('settings.resource-naming', [
            'singular' => Setting::get('resource_term_singular', config('app.api.resource_term_singular'), $resourceType),
            'plural' => Setting::get('resource_term_plural', config('app.api.resource_term_plural'), $resourceType),
        ]);
    }

    public function categories(ResourceType $resourceType): View
    {
        // Two waves: the categories (and the nav's needs), then each one's subcategories.
        $first = $this->api->pool(fn (RequestPool $pool) => $pool->navigation()->categories());

        $categories = $this->content($first[RequestPool::CATEGORIES]);

        $second = $this->api->pool(fn (RequestPool $pool) => $this->poolSubcategories($pool, $categories));

        $categories = collect($categories)->map(function (array $category) use ($second) {
            $category['subcategories'] = $this->content($second['subcategories.'.$category['id']]);

            return $category;
        })->all();

        return view('settings.categories', [
            'categories' => $categories,
        ]);
    }

    public function periods(ResourceType $resourceType): View
    {
        return view('settings.periods', [
            'periods' => ReportingPeriod::query()->where('resource_type_id', $resourceType->id)->orderBy('sort_order')->get(),
        ]);
    }
}
