<?php

declare(strict_types=1);

namespace App\Actions\ResourceType;

use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Support\Collection;

/**
 * The resource types the signed-in user currently has access to - driven by
 * the API's own permission system (GET /v3/auth/user/permitted-resource-types),
 * intersected with the resource types this app knows about locally. A
 * resource type permitted on the API but never created through this app
 * (e.g. shared in from elsewhere) won't appear yet - see the note in
 * ResourceTypeController::index().
 */
class VisibleResourceTypes
{
    public function __construct(private readonly ApiService $api) {}

    /**
     * @return Collection<int, ResourceType>
     */
    public function __invoke(): Collection
    {
        $response = $this->api->permittedResourceTypes();

        if ($response['status'] !== 200) {
            return collect();
        }

        $permittedIds = collect($response['content'])->pluck('id')->all();

        return ResourceType::query()
            ->whereIn('api_resource_type_id', $permittedIds)
            ->orderBy('sort_order')
            ->get();
    }
}
