<?php

declare(strict_types=1);

namespace App\Service\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http as HttpFacade;
use Throwable;

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
     * "pool" is the id of the pool the request was sent in (a key of
     * self::$pools), null for one sent on its own.
     *
     * @var array<int, array{method: string, uri: string, status: int, time: int, shape: string, pool: int|null}>
     */
    private static array $requests = [];

    /**
     * Every pool sent during this process's lifetime, by id (from 1), with
     * how many requests it held and how long it took to complete as a whole.
     *
     * @var array<int, array{size: int, time: int}>
     */
    private static array $pools = [];

    public function __construct(private readonly ?string $bearer = null)
    {
        $this->client = $this->configure(HttpFacade::createPendingRequest());
    }

    /**
     * @return array<int, array{method: string, uri: string, status: int, time: int, shape: string, pool: int|null}>
     */
    public static function requests(): array
    {
        return self::$requests;
    }

    /**
     * @return array<int, array{size: int, time: int}>
     */
    public static function pools(): array
    {
        return self::$pools;
    }

    /**
     * How long the requests took as far as the caller is concerned. Pooled
     * requests overlap, so "time" counts each sequential request plus each
     * pool's own duration rather than adding every request up, and "saved"
     * is what pooling is estimated to have bought - the time the pooled
     * requests would have taken one after another, less what the pools took.
     *
     * @return array{requests: int, pooled: int, pools: int, time: int, saved: int}
     */
    public static function summary(): array
    {
        $sequential = array_filter(self::$requests, static fn (array $request) => $request['pool'] === null);
        $pooled = array_filter(self::$requests, static fn (array $request) => $request['pool'] !== null);
        $poolTime = array_sum(array_column(self::$pools, 'time'));

        return [
            'requests' => count(self::$requests),
            'pooled' => count($pooled),
            'pools' => count(self::$pools),
            'time' => array_sum(array_column($sequential, 'time')) + $poolTime,
            'saved' => max(0, array_sum(array_column($pooled, 'time')) - $poolTime),
        ];
    }

    /**
     * Forgets the requests made so far - for tests, which share the process.
     */
    public static function reset(): void
    {
        self::$requests = [];
        self::$pools = [];
    }

    public function get(string $uri): array
    {
        $start = microtime(true);

        return $this->respond($this->client->get($uri), 'GET', $uri, $start);
    }

    public function head(string $uri): array
    {
        $start = microtime(true);

        return $this->respond($this->client->head($uri), 'HEAD', $uri, $start);
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
     * Sends the requests concurrently (at most api.pool_concurrency at a
     * time) and returns each normalised response under the key it was given,
     * in the order the requests were given. Only GET and HEAD can be
     * pooled, see PoolRequest.
     *
     * Like the individual methods, a request the API answers with an error
     * status is just a response - but one that couldn't be made at all
     * (e.g. the API is unreachable) throws, rather than coming back as
     * a value in the results.
     *
     * @param  array<string, PoolRequest>  $requests
     * @return array<string, array{status: int, content: mixed, fields: array}>
     *
     * @throws ConnectionException
     */
    public function pool(array $requests): array
    {
        // A pool of one is just a request, so it isn't reported as a pool.
        if (count($requests) <= 1) {
            return array_map(
                fn (PoolRequest $request) => $request->method === 'HEAD' ? $this->head($request->uri) : $this->get($request->uri),
                $requests
            );
        }

        $start = microtime(true);

        $responses = HttpFacade::pool(function (Pool $pool) use ($requests) {
            foreach ($requests as $key => $request) {
                $pending = $this->configure($pool->as((string) $key));

                $request->method === 'HEAD' ? $pending->head($request->uri) : $pending->get($request->uri);
            }
        }, (int) Config::get('app.api.pool_concurrency'));

        $duration = $this->elapsed($start);

        foreach ($responses as $response) {
            if ($response instanceof Throwable) {
                throw $response;
            }
        }

        $pool = count(self::$pools) + 1;
        self::$pools[$pool] = ['size' => count($requests), 'time' => $duration];

        $normalised = [];

        foreach ($requests as $key => $request) {
            $response = $responses[$key];

            // The client's own timing for the request, falling back to the
            // pool's for one that doesn't have any (e.g. a faked response).
            $time = $response->transferStats !== null
                ? (int) round($response->transferStats->getTransferTime() * 1000)
                : $duration;

            $normalised[$key] = $this->record($response, $request->method, $request->uri, $time, $pool);
        }

        return $normalised;
    }

    private function configure(PendingRequest $request): PendingRequest
    {
        $request = $request
            ->baseUrl(Config::get('app.api.base_url'))
            ->acceptJson()
            ->asJson();

        return $this->bearer !== null
            ? $request->withToken($this->bearer)
            : $request;
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
        return $this->record($response, $method, $uri, $this->elapsed($start), null);
    }

    private function record(Response $response, string $method, string $uri, int $time, ?int $pool): array
    {
        $content = $response->json();

        self::$requests[] = [
            'method' => $method,
            'uri' => $uri,
            'status' => $response->status(),
            'time' => $time,
            'shape' => $this->describeShape($content),
            'pool' => $pool,
        ];

        return [
            'status' => $response->status(),
            'content' => $content,
            'fields' => $response->json('fields') ?? [],
        ];
    }

    private function elapsed(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
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
