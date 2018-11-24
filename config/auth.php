<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'user'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    | NOTE: "token" driver is not supported in JWT Auth
    |
    */

    'guards' => [
        'user' => [
            'provider' => 'user',
            'driver' => 'session',
        ],
        'customer' => [
            'provider' => 'customer',
            'driver' => 'session',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'user' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],
        'customer' => [
            'driver' => 'eloquent',
            'model' => App\Customer::class,
        ],
    ],

    'passwords' => [
        'user' => [
            'provider' => 'user',
            'email' => 'mails.confirmation.customer',
            'table' => 'password_resets',
            'expire' => 60,
        ],

        'customer' => [
            'provider' => 'customer',
            'email' => 'mails.confirmation.customer',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ]

];
