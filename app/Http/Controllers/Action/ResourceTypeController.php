<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\ApiActionResult;
use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResourceTypeController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('resource_types', 'name')->where('user_id', Auth::id())],
            'description' => ['required', 'string'],
            'item_type' => ['required', 'string', 'in:allocated-expense,allocated-transaction'],
        ]);

        $itemTypesResponse = $this->api->itemTypes();

        if ($itemTypesResponse['status'] !== 200) {
            return $this->redirectForApiResult(ApiActionResult::failed('Unable to reach the API.'), 'resource-types.create');
        }

        $itemType = collect($itemTypesResponse['content'])->firstWhere('name', $validated['item_type']);

        $response = $this->api->createResourceType($validated['name'], $validated['description'], $itemType['id']);

        if ($response['status'] === 422) {
            return $this->redirectForApiResult(ApiActionResult::validationFailed($response['fields']), 'resource-types.create');
        }

        if ($response['status'] !== 201) {
            return $this->redirectForApiResult(ApiActionResult::failed('Unexpected status '.$response['status']), 'resource-types.create');
        }

        $subtypesResponse = $this->api->itemSubtypes($itemType['id']);
        $itemSubtypeId = $subtypesResponse['status'] === 200 ? ($subtypesResponse['content'][0]['id'] ?? null) : null;

        $resourceType = ResourceType::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'item_type' => $validated['item_type'],
            'api_resource_type_id' => $response['content']['id'],
            'api_item_type_id' => $itemType['id'],
            'item_subtype_id' => $itemSubtypeId,
            'sort_order' => ResourceType::query()->max('sort_order') + 1,
        ]);

        return $this->redirectForApiResult(
            ApiActionResult::success(),
            'dashboard',
            ['resourceType' => $resourceType],
            "{$resourceType->name} has been created.",
        );
    }
}
