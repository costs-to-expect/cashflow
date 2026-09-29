<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiService::class, function () {
            // Route model binding hasn't necessarily run yet whenever this is
            // first resolved (e.g. UserProvider used to trigger this during
            // the "auth" middleware, which runs before it) - guard against
            // getting the raw un-substituted route parameter instead of the
            // real model.
            $resourceType = request()->route('resourceType');
            $resourceType = $resourceType instanceof ResourceType ? $resourceType : null;

            return new ApiService(
                request()->cookie(config('app.api.cookie_bearer')),
                $resourceType?->api_resource_type_id,
                $resourceType?->item_subtype_id,
            );
        });
    }
}
