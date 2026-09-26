<?php

declare(strict_types=1);

namespace App\Service\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
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

    /**
     * Every request made during this process's lifetime, for the
     * "API requests" transparency panel (see components/layout/requests).
     *
     * @var array<int, array{method: string, uri: string, status: int, time: int, shape: string}>
     */
    private static array $requests = [];

    public function __construct(private readonly ?string $bearer = null)
    {
        $client = HttpFacade::baseUrl(Config::get('app.api.base_url'))
            ->acceptJson()
            ->asJson();

        $this->client = $this->bearer !== null
            ? $client->withToken($this->bearer)
            : $client;
    }

    /**
     * @return array<int, array{method: string, uri: string, status: int, time: int, shape: string}>
     */
    public static function requests(): array
    {
        return self::$requests;
    }

    public function get(string $uri): array
    {
        $start = microtime(true);

        return $this->respond($this->client->get($uri), 'GET', $uri, $start);
    }

    public function post(string $uri, array $payload = []): array
    {
        $start = microtime(true);

        return $this->respond($this->client->post($uri, $this->withoutNulls($payload)), 'POST', $uri, $start);
    }

    public function patch(string $uri, array $payload = []): array
    {
        $start = microtime(true);

        return $this->respond($this->client->patch($uri, $this->withoutNulls($payload)), 'PATCH', $uri, $start);
    }

    public function delete(string $uri): array
    {
        $start = microtime(true);

        return $this->respond($this->client->delete($uri), 'DELETE', $uri, $start);
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

    private function respond(Response $response, string $method, string $uri, float $start): array
    {
        $content = $response->json();

        self::$requests[] = [
            'method' => $method,
            'uri' => $uri,
            'status' => $response->status(),
            'time' => (int) round((microtime(true) - $start) * 1000),
            'shape' => $this->describeShape($content),
        ];

        return [
            'status' => $response->status(),
            'content' => $content,
            'fields' => $response->json('fields') ?? [],
        ];
    }

    private function describeShape(mixed $content): string
    {
        if (is_array($content) && array_is_list($content)) {
            return count($content).' item'.(count($content) === 1 ? '' : 's');
        }

        if (is_array($content)) {
            return 'object';
        }

        return 'empty';
    }
}
