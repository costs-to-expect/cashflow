<?php

declare(strict_types=1);

namespace App\Auth\Guard\Api;

use App\Service\Api\ApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;

/**
 * There's no local users table - every request re-resolves the signed-in
 * user from the API using the bearer token cookie (wired into ApiService by
 * ApiServiceProvider). The allow-list is re-checked here too, not just at
 * sign-in, so revoking an email takes effect immediately.
 */
class UserProvider implements UserProviderContract
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $response = app(ApiService::class)->currentUser();

        if ($response['status'] !== 200) {
            return null;
        }

        $content = $response['content'];

        if ($content === null || (string) $content['id'] !== (string) $identifier) {
            return null;
        }

        if (! in_array(strtolower((string) $content['email']), config('app.api.allowed_emails'), true)) {
            return null;
        }

        $user = new User;
        $user->id = (string) $content['id'];
        $user->name = (string) $content['name'];
        $user->email = (string) $content['email'];

        return $user;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return $this->retrieveById($identifier);
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
