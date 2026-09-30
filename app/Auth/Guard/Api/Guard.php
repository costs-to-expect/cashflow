<?php

declare(strict_types=1);

namespace App\Auth\Guard\Api;

use App\Service\Api\ApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class Guard implements GuardContract
{
    private ?Authenticatable $user = null;

    private array $errors = [];

    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly Request $request,
    ) {}

    /**
     * $remember controls how long the bearer/user cookies last: checked
     * gives the usual 30-day persistent cookie, unchecked gives a plain
     * session cookie (Cookie::queue's $minutes = 0) that goes away when the
     * browser closes.
     */
    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        $email = strtolower(trim($email));

        if (! in_array($email, config('app.api.allowed_emails'), true)) {
            $this->errors = ['email' => ['Those credentials are not recognised.']];

            return false;
        }

        $response = (new ApiService)->signIn($email, $password);

        if ($response['status'] !== 201) {
            $this->errors = $response['fields'] !== []
                ? $response['fields']
                : ['email' => ['Those credentials are not recognised.']];

            return false;
        }

        $lifetime = $remember ? 60 * 24 * 30 : 0;

        Cookie::queue(config('app.api.cookie_bearer'), (string) $response['content']['token'], $lifetime);
        Cookie::queue(config('app.api.cookie_user'), (string) $response['content']['id'], $lifetime);

        return true;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->request->cookie(config('app.api.cookie_user'));

        if ($userId === null) {
            return null;
        }

        $this->user = $this->userProvider->retrieveById($userId);

        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return $this->attempt($credentials['email'] ?? '', $credentials['password'] ?? '');
    }

    public function hasUser(): bool
    {
        return $this->user instanceof Authenticatable;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function logout(): void
    {
        Cookie::queue(Cookie::forget(config('app.api.cookie_bearer')));
        Cookie::queue(Cookie::forget(config('app.api.cookie_user')));

        $this->user = null;
    }
}
