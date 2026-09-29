<?php

namespace App\Providers;

use App\Actions\ResourceType\VisibleResourceTypes;
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
            $resourceTypes = collect();

            if (Auth::check()) {
                // No resource type on routes outside one (e.g. the
                // resource-type picker/create screens) - nothing to list
                // resources for there.
                if ($this->currentResourceType() !== null) {
                    $response = app(ApiService::class)->resources();
                    $resources = $response['status'] === 200 ? $response['content'] : [];
                }

                $resourceTypes = app(VisibleResourceTypes::class)();
            }

            $view->with('navResources', $resources);
            $view->with('allResourceTypes', $resourceTypes);
        });

        View::composer('*', function ($view) {
            $currentResourceType = $this->currentResourceType();

            $view->with('currentResourceType', $currentResourceType);
            $view->with('resourceTermSingular', Setting::get('resource_term_singular', config('app.api.resource_term_singular'), $currentResourceType));
            $view->with('resourceTermPlural', Setting::get('resource_term_plural', config('app.api.resource_term_plural'), $currentResourceType));
            $view->with('version', config('app.version'));
        });
    }

    /**
     * Route model binding hasn't necessarily run yet whenever this is called
     * (e.g. from the "auth" middleware, which runs before it) - guard against
     * getting the raw un-substituted route parameter instead of the real
     * model.
     */
    private function currentResourceType(): ?ResourceType
    {
        $resourceType = request()->route('resourceType');

        return $resourceType instanceof ResourceType ? $resourceType : null;
    }
}
