<?php

declare(strict_types=1);

namespace App\Actions\ResourceType;

use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The resource types the signed-in user currently has access to - driven by
 * the API's own permission system (GET /v3/auth/user/permitted-resource-types).
 *
 * This app keeps a local row per resource type (for its settings, reporting
 * periods and so on). Anything the API permits that has no row yet - made
 * through another app, shared in from elsewhere, or a local database that has
 * been reset - gets one here, so signing in always shows what already exists
 * in the API for the user. Only the item types this app handles are linked.
 */
class VisibleResourceTypes
{
    private const ITEM_TYPES = ['allocated-expense', 'allocated-transaction'];

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

        $permitted = collect($response['content']);

        $this->linkMissing($permitted);

        return ResourceType::query()
            ->whereIn('api_resource_type_id', $permitted->pluck('id')->all())
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $permitted
     */
    private function linkMissing(Collection $permitted): void
    {
        $known = ResourceType::query()
            ->whereIn('api_resource_type_id', $permitted->pluck('id')->all())
            ->pluck('api_resource_type_id')
            ->all();

        $missing = $permitted->filter(
            fn (array $apiResourceType) => ! in_array($apiResourceType['id'], $known, true)
                && in_array($apiResourceType['item_type']['name'] ?? null, self::ITEM_TYPES, true)
        );

        if ($missing->isEmpty()) {
            return;
        }

        $sortOrder = (int) ResourceType::query()->max('sort_order');
        $subtypes = [];

        foreach ($missing as $apiResourceType) {
            $itemType = $apiResourceType['item_type'];

            // One lookup per item type, the first subtype is what a type created here would get.
            $subtypes[$itemType['id']] ??= $this->firstSubtypeId($itemType['id']);

            if ($subtypes[$itemType['id']] === null) {
                continue;
            }

            try {
                ResourceType::firstOrCreate(
                    ['api_resource_type_id' => $apiResourceType['id']],
                    [
                        'user_id' => Auth::id(),
                        'name' => $apiResourceType['name'],
                        'description' => (string) ($apiResourceType['description'] ?? ''),
                        'item_type' => $itemType['name'],
                        'api_item_type_id' => $itemType['id'],
                        'item_subtype_id' => $subtypes[$itemType['id']],
                        'sort_order' => ++$sortOrder,
                    ]
                );
            } catch (UniqueConstraintViolationException) {
                // The user already has a different resource type with this name here; leave it unlinked.
            }
        }
    }

    private function firstSubtypeId(string $itemTypeId): ?string
    {
        $response = $this->api->itemSubtypes($itemTypeId);

        return $response['status'] === 200 ? ($response['content'][0]['id'] ?? null) : null;
    }
}
