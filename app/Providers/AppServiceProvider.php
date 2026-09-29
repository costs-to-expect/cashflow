<?php

namespace App\Providers;

use App\Models\ResourceType;
use App\Models\Setting;
use App\Service\Api\ApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view) {
            $resources = [];

            // No resource type on routes outside one (e.g. the resource-type
            // picker/create screens) - nothing to list resources for there.
            if (Auth::check() && request()->route('resourceType') !== null) {
                $response = app(ApiService::class)->resources();
                $resources = $response['status'] === 200 ? $response['content'] : [];
            }

            $view->with('navResources', $resources);
        });

        View::composer('*', function ($view) {
            /** @var ResourceType|null $currentResourceType */
            $currentResourceType = request()->route('resourceType');

            $view->with('currentResourceType', $currentResourceType);
            $view->with('resourceTermSingular', Setting::get('resource_term_singular', config('app.api.resource_term_singular'), $currentResourceType));
            $view->with('resourceTermPlural', Setting::get('resource_term_plural', config('app.api.resource_term_plural'), $currentResourceType));
            $view->with('version', config('app.version'));
        });
    }
}
