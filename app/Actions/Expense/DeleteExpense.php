<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

class DeleteExpense
{
    public function __construct(private readonly ApiService $api) {}

    public function __invoke(string $resourceId, string $itemId): ApiActionResult
    {
        $response = $this->api->deleteItem($resourceId, $itemId);

        if ($response['status'] === 204) {
            return ApiActionResult::success();
        }

        return ApiActionResult::failed('Unexpected status '.$response['status'].' deleting the expense.');
    }
}
