<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    | The paths that should be subject to CORS handling. Use specific paths for
    | better security, but '*' works for all API routes if needed.
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Explicitly include API and Sanctum CSRF routes

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    | HTTP methods allowed for CORS requests. '*' allows all methods (GET, POST, etc.).
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | The origins allowed to make CORS requests. Include all frontend URLs,
    | including localhost for development.
    */
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:8080'), // Local development URL
        'http://127.0.0.1:8000', // Laravel server localhost (for Swagger UI)
        'https://animal-haven-manager.app.genez.io', // Existing production URL
        'https://animal-haven-manager.vercel.app', // New Vercel URL
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origin Patterns
    |--------------------------------------------------------------------------
    | Regex patterns for dynamic origins, if needed. Empty array is fine for static origins.
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    | Headers allowed in CORS requests. '*' allows all headers.
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    | Headers exposed to the client in the response. Usually empty unless specific headers are needed.
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    | How long (in seconds) the CORS preflight response can be cached. 0 disables caching.
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    | Whether to allow credentials (cookies, authorization headers) in CORS requests.
    | Must be true for Sanctum CSRF and auth to work.
    */
    'supports_credentials' => true,
];
