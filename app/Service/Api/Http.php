<?php

declare(strict_types=1);

namespace App\Service\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http as HttpFacade;

/**
 * Thin wrapper around Laravel's HTTP client for talking to the Costs to
 * Expect API. Normalises every response down to status/content/fields so
 * callers never need to touch a Response object directly.
 */
class Http
{
    private PendingRequest $client;

    public function __construct(private readonly ?string $bearer = null)
    {
        $client = HttpFacade::baseUrl(Config::get('api.base_url'))
            ->acceptJson()
            ->asJson();

        $this->client = $this->bearer !== null
            ? $client->withToken($this->bearer)
            : $client;
    }

    public function get(string $uri): array
    {
        return $this->respond($this->client->get($uri));
    }

    public function post(string $uri, array $payload = []): array
    {
        return $this->respond($this->client->post($uri, $this->withoutNulls($payload)));
    }

    public function patch(string $uri, array $payload = []): array
    {
        return $this->respond($this->client->patch($uri, $this->withoutNulls($payload)));
    }

    /**
     * The API treats an explicit JSON null differently from an omitted key
     * for its optional fields (e.g. description) - null fails validation,
     * omitting the key is accepted. Every optional field we might send as
     * null (a blank form field) needs to be dropped rather than passed through.
     */
    private function withoutNulls(array $payload): array
    {
        return array_filter($payload, static fn ($value) => $value !== null);
    }

    public function delete(string $uri): array
    {
        return $this->respond($this->client->delete($uri));
    }

    private function respond(\Illuminate\Http\Client\Response $response): array
    {
        return [
            'status' => $response->status(),
            'content' => $response->json(),
            'fields' => $response->json('fields') ?? [],
        ];
    }
}
