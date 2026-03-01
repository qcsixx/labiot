<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the session driver that will be used by your
    | application. Laravel supports a variety of session backends such as
    | "file", "cookie", "database", "apc", "memcached", "redis", and "array".
    | You may specify one of these drivers below to use during your session.
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),
];
