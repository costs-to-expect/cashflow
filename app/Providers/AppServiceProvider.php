<?php

namespace App\Providers;

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

            if (Auth::check()) {
                $response = app(ApiService::class)->resources();
                $resources = $response['status'] === 200 ? $response['content'] : [];
            }

            $view->with('navResources', $resources);
        });

        View::composer('*', function ($view) {
            $view->with('resourceTermSingular', Setting::get('resource_term_singular', config('app.api.resource_term_singular')));
            $view->with('resourceTermPlural', Setting::get('resource_term_plural', config('app.api.resource_term_plural')));
            $view->with('version', config('app.version'));
        });
    }
}
