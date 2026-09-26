<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

class UpdateCategory
{
    public function __construct(private readonly ApiService $api) {}

    public function __invoke(string $categoryId, string $name, string $description): ApiActionResult
    {
        $response = $this->api->updateCategory($categoryId, $name, $description);

        if ($response['status'] === 204) {
            return ApiActionResult::success();
        }

        if ($response['status'] === 422) {
            return ApiActionResult::validationFailed($response['fields']);
        }

        return ApiActionResult::failed('Unexpected status '.$response['status'].' updating the category.');
    }
}
