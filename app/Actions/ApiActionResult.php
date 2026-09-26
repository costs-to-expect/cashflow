<?php

declare(strict_types=1);

namespace App\Actions;

/**
 * The consistent shape every Action in this app returns, so controllers can
 * turn the result into a redirect without knowing the specifics of the API
 * call that was made (see Controller::redirectForApiResult()).
 */
final class ApiActionResult
{
    /**
     * @param  array<string, array<int, string>>  $fieldErrors
     */
    public function __construct(
        public readonly bool $ok,
        public readonly array $fieldErrors = [],
        public readonly string $message = '',
        public readonly array $data = [],
    ) {}

    public static function success(array $data = []): self
    {
        return new self(ok: true, data: $data);
    }

    public static function validationFailed(array $fieldErrors): self
    {
        return new self(ok: false, fieldErrors: $fieldErrors);
    }

    public static function failed(string $message): self
    {
        return new self(ok: false, message: $message);
    }
}
