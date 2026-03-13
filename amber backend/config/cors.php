<?php

return [
    'paths' => ['api/*', 'api/v2/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Temporarily allow all origins for testing
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 0, // Disable caching during testing
    'supports_credentials' => true,
];
