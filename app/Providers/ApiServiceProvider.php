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
            /** @var ResourceType|null $resourceType */
            $resourceType = request()->route('resourceType');

            return new ApiService(
                request()->cookie(config('app.api.cookie_bearer')),
                $resourceType?->api_resource_type_id,
                $resourceType?->item_subtype_id,
            );
        });
    }
}
