<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

class CreateSubcategory
{
    public function __construct(private readonly ApiService $api) {}

    public function __invoke(string $categoryId, string $name, string $description): ApiActionResult
    {
        $response = $this->api->createSubcategory($categoryId, $name, $description);

        if (in_array($response['status'], [200, 201], true)) {
            return ApiActionResult::success(['subcategory' => $response['content']]);
        }

        if ($response['status'] === 422) {
            return ApiActionResult::validationFailed($response['fields']);
        }

        return ApiActionResult::failed('Unexpected status '.$response['status'].' creating the subcategory.');
    }
}
