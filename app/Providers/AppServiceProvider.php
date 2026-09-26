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
            $children = [];

            if (Auth::check()) {
                $response = app(ApiService::class)->resources();
                $children = $response['status'] === 200 ? $response['content'] : [];
            }

            $view->with('navChildren', $children);
        });

        View::composer('*', function ($view) {
            $view->with('resourceTermSingular', Setting::get('resource_term_singular', config('api.resource_term_singular')));
            $view->with('resourceTermPlural', Setting::get('resource_term_plural', config('api.resource_term_plural')));
        });
    }
}
