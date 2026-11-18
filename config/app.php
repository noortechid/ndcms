<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'ND Coffee Management System'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(
        env('APP_DEBUG', false),
        FILTER_VALIDATE_BOOLEAN
    ),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'session' => [
        'name' => 'NDCOFFEE_SESSION',
        'lifetime' => 7200,
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
