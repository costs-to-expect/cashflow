<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Costs to Expect API
    |--------------------------------------------------------------------------
    |
    | Connection details for the Costs to Expect API instance this app talks
    | to. Resource types themselves (which API resource type each one maps
    | to, allocated-expense vs allocated-transaction) are stored locally in
    | the resource_types table, not configured here - see
    | App\Models\ResourceType.
    |
    */

    'base_url' => env('API_URL', 'http://localhost:8080'),

    'default_currency_id' => env('API_DEFAULT_CURRENCY_ID'),

    /*
    |--------------------------------------------------------------------------
    | Resource terminology
    |--------------------------------------------------------------------------
    |
    | What a "resource" is called in the UI before a resource type has its
    | own naming set from Settings. Defaults to "Child"/"Children" for this
    | family, but another resource type could be tracking anything (products,
    | projects, ...) and name its resources accordingly.
    |
    */

    'resource_term_singular' => env('APP_RESOURCE_TERM', 'Child'),

    'resource_term_plural' => env('APP_RESOURCE_TERM_PLURAL', 'Children'),

    /*
    |--------------------------------------------------------------------------
    | Background service token
    |--------------------------------------------------------------------------
    |
    | The recurring-expense scheduler runs outside any signed-in user's
    | browser session, so it can't use the bearer-token cookie. It uses this
    | long-lived API token instead (generate one by signing in once via
    | POST /v3/auth/login and keep the returned token here).
    |
    */

    'service_token' => env('API_SERVICE_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Sign-in allow-list
    |--------------------------------------------------------------------------
    |
    | Only these email addresses are allowed to sign in, regardless of
    | whether the API accepts their credentials. Comma separated in .env.
    |
    */

    'allowed_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('APP_ALLOWED_EMAILS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Session cookies
    |--------------------------------------------------------------------------
    |
    | The bearer token and API user id are stored in their own encrypted
    | cookies rather than the framework session, so the custom guard can
    | resolve the current user without a local users table.
    |
    */

    'cookie_bearer' => env('API_COOKIE_BEARER', 'cte_bearer'),

    'cookie_user' => env('API_COOKIE_USER', 'cte_user'),

];
