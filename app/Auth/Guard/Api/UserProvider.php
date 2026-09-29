<?php

declare(strict_types=1);

namespace App\Auth\Guard\Api;

use App\Service\Api\ApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;

/**
 * There's no local users table - every request re-resolves the signed-in
 * user from the API using the bearer token cookie. The allow-list is
 * re-checked here too, not just at sign-in, so revoking an email takes
 * effect immediately.
 *
 * Builds its own ApiService directly rather than resolving the container's
 * shared singleton (App\Providers\ApiServiceProvider) - the "auth" middleware
 * that triggers this runs before Laravel's route-model binding, so on a
 * {resourceType}-prefixed route the singleton would otherwise get built (and
 * locked in, since it's a singleton) from the raw un-substituted route
 * parameter instead of the real ResourceType model. currentUser() doesn't
 * need a resource type anyway.
 */
class UserProvider implements UserProviderContract
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $api = new ApiService(request()->cookie(config('app.api.cookie_bearer')));

        $response = $api->currentUser();

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
