<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Models\ReportingPeriod;
use App\Models\Setting;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(): View
    {
        return view('settings.index');
    }

    public function defaultSplit(): View
    {
        $resources = $this->api->resources();

        return view('settings.default-split', [
            'children' => $resources['status'] === 200 ? $resources['content'] : [],
            'allocations' => DefaultSplitAllocation::query()->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }

    public function resourceNaming(): View
    {
        return view('settings.resource-naming', [
            'singular' => Setting::get('resource_term_singular', config('app.api.resource_term_singular')),
            'plural' => Setting::get('resource_term_plural', config('app.api.resource_term_plural')),
        ]);
    }

    public function categories(): View
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

    public function periods(): View
    {
        return view('settings.periods', [
            'periods' => ReportingPeriod::query()->orderBy('sort_order')->get(),
        ]);
    }
}
