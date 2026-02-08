<?php

return [
    'name' => env('APP_NAME', 'Admin ISP'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => 'es',
    'fallback_locale' => 'en',
    'charset' => 'UTF-8',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
];

