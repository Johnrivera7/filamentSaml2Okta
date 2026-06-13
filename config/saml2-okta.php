<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAML2 Okta Plugin Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the SAML2 Okta plugin.
    | You can customize these settings according to your needs.
    |
    */

    'defaults' => [
        'button_label' => 'Login with SAML2',
        'button_icon' => 'heroicon-o-shield-check',
        'auto_create_users' => true,
        'auto_update_users' => true,
        'mark_as_external' => true,
        'default_role' => 'user',
        'okta_id_field' => 'okta_id',
        'debug_mode' => false,
    ],

    'certificates' => [
        'key_size' => 2048,
        'digest_alg' => 'sha256',
        'x509_extensions' => 'v3_ca',
        'days' => 365,
    ],

    'logging' => [
        'channel' => 'saml2',
        'level' => 'debug',
    ],

    'navigation' => [
        'group' => 'SAML2',
        'sort' => 1,
    ],

    'middleware' => [
        'web',
    ],
];