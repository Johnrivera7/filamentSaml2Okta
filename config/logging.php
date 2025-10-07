<?php

return [
    'channels' => [
        'saml2-okta' => [
            'driver' => 'daily',
            'path' => storage_path('logs/saml2-okta.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
    ],
];
