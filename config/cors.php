<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/google', 'auth/google/callback'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Development local
        'http://localhost:5173',
        'http://127.0.0.1:5173',

        // Docker frontend — sesuaikan dengan port Nginx kamu di compose (8000, bukan 8080)
        'http://localhost:8000',

        // Production — isi nanti jika sudah punya domain
        // 'https://jobportal.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,

];
