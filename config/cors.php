<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://raw-disposal.test',
        'https://raw-disposal.test',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8000',
        'http://localhost:8081', // Expo
        'http://localhost:19000', // Expo
        'http://localhost:19001', // Expo
        'http://localhost:19002', // Expo
        'http://127.0.0.1:8000',
        'http://10.0.2.2:8000', // Android emulator
        'http://192.168.1.104:8081', // Expo on physical device
        'exp://192.168.1.104:8081', // Expo protocol
    ],

    'allowed_origins_patterns' => [
        '#^http://192\.168\.\d+\.\d+:\d+$#', // Local network IPs
        '#^exp://.*$#', // Expo development
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
