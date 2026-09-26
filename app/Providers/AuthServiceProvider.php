<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\Guard\Api\Guard;
use App\Auth\Guard\Api\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::provider('api', function () {
            return new UserProvider;
        });

        Auth::extend('api', function ($app, string $name, array $config) {
            return new Guard(
                $app['auth']->createUserProvider($config['provider']),
                $app['request'],
            );
        });
    }
}
