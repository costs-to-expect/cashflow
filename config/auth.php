<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | There's no local users table - the "web" guard is a custom driver
    | (see App\Auth\Guard\Api and AuthServiceProvider) that authenticates
    | against the Costs to Expect API itself and re-resolves the signed-in
    | user from a bearer token cookie on every request. No registration or
    | password reset flow exists in this app - that's handled by the API.
    |
    */

    'defaults' => [
        'guard' => 'web',
    ],

    'guards' => [
        'web' => [
            'driver' => 'api',
            'provider' => 'api',
        ],
    ],

    'providers' => [
        'api' => [
            'driver' => 'api',
        ],
    ],

];
