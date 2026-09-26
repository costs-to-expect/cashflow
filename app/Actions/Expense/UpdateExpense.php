<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\ApiActionResult;
use App\Service\Api\ApiService;

class UpdateExpense
{
    public function __construct(private readonly ApiService $api) {}

    public function __invoke(string $resourceId, string $itemId, array $payload): ApiActionResult
    {
        if (array_key_exists('total', $payload)) {
            $payload['total'] = number_format((float) $payload['total'], 2, '.', '');
        }

        $response = $this->api->updateItem($resourceId, $itemId, $payload);

        if ($response['status'] === 204) {
            return ApiActionResult::success();
        }

        if ($response['status'] === 422) {
            return ApiActionResult::validationFailed($response['fields']);
        }

        return ApiActionResult::failed('Unexpected status '.$response['status'].' updating the expense.');
    }
}
