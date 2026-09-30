<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Models\Setting;
use App\Service\Api\ApiService;
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
        $response = $this->api->resources();

        return view('settings.default-split', [
            'resources' => $response['status'] === 200 ? $response['content'] : [],
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
        $categoriesResponse = $this->api->categories();
        $categories = $categoriesResponse['status'] === 200 ? $categoriesResponse['content'] : [];

        $categories = collect($categories)->map(function (array $category) {
            $subcategoriesResponse = $this->api->subcategories($category['id']);
            $category['subcategories'] = $subcategoriesResponse['status'] === 200 ? $subcategoriesResponse['content'] : [];

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
