<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
    ])),
    // Allow all Vercel preview and production URLs
    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
        '#^https://.*\.netlify\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 86400,
    'supports_credentials' => true,
];
