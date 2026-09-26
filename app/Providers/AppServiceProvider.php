<?php

namespace App\Providers;

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
    }
}
