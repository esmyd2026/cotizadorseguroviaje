<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo credentials
    |--------------------------------------------------------------------------
    |
    | Read once from .env here (not with env() scattered across seeders and
    | views) so the values stay correct under config caching. The login
    | screen only ever displays these when APP_ENV=local — see
    | resources/views/auth/login.blade.php.
    |
    */

    'admin_email' => env('ADMIN_EMAIL', 'admin@gestionsegura.test'),

    'admin_password' => env('ADMIN_PASSWORD'),

    'customer_document_id' => '1710034065',

    // Computed once from APP_ENV here (rather than an app()->environment()
    // call in the view) so the login screen's visibility rule is a plain
    // config value — easy to assert and to override in tests.
    'show_credentials' => env('APP_ENV', 'production') === 'local',

];
