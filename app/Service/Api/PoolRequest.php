<?php

declare(strict_types=1);

namespace App\Service\Api;

/**
 * A request that can be sent as part of a pool. A pool runs its requests
 * concurrently and in no guaranteed order, which is only safe for requests
 * that don't change anything - so only GET and HEAD can be pooled, and
 * there's deliberately no way to build one for any other method.
 */
final class PoolRequest
{
    private function __construct(
        public readonly string $method,
        public readonly string $uri,
    ) {}

    public static function get(string $uri): self
    {
        return new self('GET', $uri);
    }

    public static function head(string $uri): self
    {
        return new self('HEAD', $uri);
    }
}
