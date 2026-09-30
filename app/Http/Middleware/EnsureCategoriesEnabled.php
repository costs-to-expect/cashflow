<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ResourceType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the categories management pages unreachable while a resource type has
 * categories turned off - there's nothing to manage until they're switched
 * back on. Must run after ResolveResourceType, which substitutes the model.
 */
class EnsureCategoriesEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $resourceType = $request->route('resourceType');

        if ($resourceType instanceof ResourceType && ! $resourceType->categoriesEnabled()) {
            return redirect()
                ->route('settings.use-categories', $resourceType)
                ->with('status', 'Turn categories on to manage them.');
        }

        return $next($request);
    }
}
