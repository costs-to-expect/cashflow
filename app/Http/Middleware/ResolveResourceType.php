<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ResourceType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Explicitly substitutes the {resourceType} route segment into its
 * ResourceType model, rather than relying on Laravel's implicit route-model
 * binding - that only fires when the destination controller method itself
 * declares a matching typed parameter, which is easy to forget and silently
 * leaves request()->route('resourceType') as the raw string for the rest of
 * the request (breaking the shared layout's nav and every ApiService
 * resolution, not just whatever that one controller method does).
 */
class ResolveResourceType
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $value = $route->parameter('resourceType');

        if (! $value instanceof ResourceType) {
            $route->setParameter('resourceType', ResourceType::where('api_resource_type_id', $value)->firstOrFail());
        }

        return $next($request);
    }
}
