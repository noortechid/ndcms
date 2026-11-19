<?php

declare(strict_types=1);

return [
    'driver' => env('DB_CONNECTION', 'mysql'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'ndcms'),
    'username' => env('DB_USERNAME', 'ndcoffe'),
    'password' => env('DB_PASSWORD', 'ndcoffe123'),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
