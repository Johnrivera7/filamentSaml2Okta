<?php

return [
    'navigation' => [
        'group' => 'SAML2',
        'settings' => 'Configuración SAML2',
        'certificates' => 'Certificados',
        'debug' => 'Debug',
        'field_mapper' => 'Mapeador de Campos',
    ],
    
    'pages' => [
        'settings' => [
            'title' => 'Configuración SAML2',
            'description' => 'Configurar autenticación SAML2 con Okta y otros proveedores',
        ],
        'certificates' => [
            'title' => 'Certificados SAML2',
            'description' => 'Gestionar certificados SAML2 y metadatos',
        ],
        'debug' => [
            'title' => 'Debug SAML2',
            'description' => 'Ver logs de debug SAML2 y analizar datos de autenticación',
        ],
        'field_mapper' => [
            'title' => 'Mapeador de Campos SAML2',
            'description' => 'Configurar mapeo de atributos SAML a campos del modelo User',
        ],
    ],
    
    'sections' => [
        'okta_config' => [
            'title' => 'Configuración de Okta',
            'description' => 'Configurar los ajustes de tu aplicación Okta',
        ],
        'app_config' => [
            'title' => 'Configuración de la Aplicación',
            'description' => 'Configurar los ajustes SAML2 de tu aplicación',
        ],
        'user_config' => [
            'title' => 'Configuración de Usuarios',
            'description' => 'Configurar creación y gestión de usuarios',
        ],
        'debug_config' => [
            'title' => 'Configuración de Debug',
            'description' => 'Configurar modo debug y logging',
        ],
        'certificates' => [
            'title' => 'Gestión de Certificados',
            'description' => 'Generar y gestionar certificados SAML2',
        ],
        'field_mapping' => [
            'title' => 'Mapeo de Campos',
            'description' => 'Configurar mapeo de atributos SAML a campos del modelo User',
        ],
        'interface_config' => [
            'title' => 'Configuración de la Interfaz',
            'description' => 'Configurar la apariencia del botón de login',
        ],
    ],
    
    'fields' => [
        'name' => 'Nombre',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'callback_url' => 'Callback URL',
        'idp_entity_id' => 'IdP Entity ID',
        'idp_sso_url' => 'IdP SSO URL',
        'idp_slo_url' => 'IdP SLO URL',
        'idp_metadata_url' => 'IdP Metadata URL',
        'idp_x509_cert' => 'Certificado X.509 IdP',
        'sp_entity_id' => 'SP Entity ID',
        'sp_x509_cert' => 'Certificado X.509 SP',
        'sp_private_key' => 'Clave Privada SP',
        'is_active' => 'Activar autenticación SAML2',
        'button_label' => 'Etiqueta del botón',
        'button_icon' => 'Icono del botón',
        'auto_create_users' => 'Auto crear usuarios',
        'auto_update_users' => 'Auto actualizar usuarios',
        'default_role' => 'Rol por defecto',
        'mark_as_external' => 'Marcar como externo',
        'okta_id_field' => 'Campo para ID de Okta',
        'debug_mode' => 'Modo Debug',
        'field_mappings' => 'Mapeo de Campos',
    ],
    
    'actions' => [
        'generate_certificates' => 'Generar Certificados',
        'regenerate_certificates' => 'Regenerar Certificados',
        'view_certificates' => 'Gestionar Certificados',
        'view_logs' => 'Ver Logs Debug',
        'view_field_mapper' => 'Mapeador de Campos',
        'view_metadata' => 'Ver Metadatos SAML2',
        'test_connection' => 'Probar Conexión',
        'save' => 'Guardar Configuración',
    ],
    
    'messages' => [
        'config_saved' => 'Configuración SAML2 guardada exitosamente',
        'certificates_generated' => 'Certificados generados exitosamente',
        'connection_tested' => 'Conexión probada exitosamente',
        'debug_enabled' => 'Modo debug habilitado',
        'debug_disabled' => 'Modo debug deshabilitado',
    ],
    
    'buttons' => [
        'login_with_okta' => 'Iniciar sesión con Okta',
        'login_with_saml2' => 'Iniciar sesión con SAML2',
    ],
];
