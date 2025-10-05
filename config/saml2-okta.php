<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAML2 Okta Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración por defecto para el plugin SAML2 Okta
    |
    */

    'default_config' => [
        'button_label' => 'Iniciar sesión con Okta',
        'button_icon' => 'heroicon-o-shield-check',
        'is_active' => false,
    ],

    'routes' => [
        'prefix' => 'saml2',
        'middleware' => ['web'],
    ],

    'navigation' => [
        'group' => 'Autenticación',
        'sort' => 1,
    ],
];
