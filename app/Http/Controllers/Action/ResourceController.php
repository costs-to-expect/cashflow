<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\ApiActionResult;
use App\Http\Controllers\Controller;
use App\Service\Api\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $response = $this->api->createResource($validated['name'], $validated['description']);

        if ($response['status'] === 201) {
            return $this->redirectForApiResult(
                ApiActionResult::success(),
                'resources.show',
                ['resource_id' => $response['content']['id']],
                "{$response['content']['name']} has been added.",
            );
        }

        if ($response['status'] === 422) {
            return $this->redirectForApiResult(ApiActionResult::validationFailed($response['fields']), 'resources.create');
        }

        return $this->redirectForApiResult(ApiActionResult::failed('Unexpected status '.$response['status']), 'resources.create');
    }
}
