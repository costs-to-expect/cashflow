<?php

declare(strict_types=1);

namespace App\Providers;

use App\Service\Api\ApiService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiService::class, function () {
            return new ApiService(request()->cookie(config('api.cookie_bearer')));
        });
    }
}
