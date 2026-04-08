<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | KnowMyCouncil Metadata
    |--------------------------------------------------------------------------
    |
    | This config is intentionally small. It provides app metadata that is
    | useful for health/version endpoints and operational tooling.
    |
    */
    'version' => env('APP_VERSION', '0.1.0-dev'),
    'commit_sha' => env('APP_COMMIT_SHA'),
    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:3000'),
];
