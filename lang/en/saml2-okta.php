<?php

return [
    'navigation' => [
        'group' => 'SAML2',
        'settings' => 'SAML2 Settings',
        'certificates' => 'Certificates',
        'debug' => 'Debug',
        'field_mapper' => 'Field Mapper',
    ],
    
    'pages' => [
        'settings' => [
            'title' => 'SAML2 Settings',
            'description' => 'Configure SAML2 authentication with Okta and other providers',
        ],
        'certificates' => [
            'title' => 'SAML2 Certificates',
            'description' => 'Manage SAML2 certificates and metadata',
        ],
        'debug' => [
            'title' => 'SAML2 Debug',
            'description' => 'View SAML2 debug logs and analyze authentication data',
        ],
        'field_mapper' => [
            'title' => 'SAML2 Field Mapper',
            'description' => 'Configure SAML attribute mapping to User model fields',
        ],
    ],
    
    'sections' => [
        'okta_config' => [
            'title' => 'Okta Configuration',
            'description' => 'Configure your Okta application settings',
        ],
        'app_config' => [
            'title' => 'Application Configuration',
            'description' => 'Configure your application SAML2 settings',
        ],
        'user_config' => [
            'title' => 'User Configuration',
            'description' => 'Configure user creation and management settings',
        ],
        'debug_config' => [
            'title' => 'Debug Configuration',
            'description' => 'Configure debug mode and logging settings',
        ],
        'certificates' => [
            'title' => 'Certificate Management',
            'description' => 'Generate and manage SAML2 certificates',
        ],
        'field_mapping' => [
            'title' => 'Field Mapping',
            'description' => 'Configure SAML attribute mapping to User model fields',
        ],
        'interface_config' => [
            'title' => 'Interface Configuration',
            'description' => 'Configure the login button appearance',
        ],
    ],
    
    'fields' => [
        'name' => 'Name',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'callback_url' => 'Callback URL',
        'idp_entity_id' => 'IdP Entity ID',
        'idp_sso_url' => 'IdP SSO URL',
        'idp_slo_url' => 'IdP SLO URL',
        'idp_metadata_url' => 'IdP Metadata URL',
        'idp_x509_cert' => 'IdP X.509 Certificate',
        'sp_entity_id' => 'SP Entity ID',
        'sp_x509_cert' => 'SP X.509 Certificate',
        'sp_private_key' => 'SP Private Key',
        'is_active' => 'Activate SAML2 Authentication',
        'button_label' => 'Button Label',
        'button_icon' => 'Button Icon',
        'auto_create_users' => 'Auto Create Users',
        'auto_update_users' => 'Auto Update Users',
        'default_role' => 'Default Role',
        'mark_as_external' => 'Mark as External',
        'okta_id_field' => 'Okta ID Field',
        'debug_mode' => 'Debug Mode',
        'field_mappings' => 'Field Mappings',
    ],
    
    'actions' => [
        'generate_certificates' => 'Generate Certificates',
        'regenerate_certificates' => 'Regenerate Certificates',
        'view_certificates' => 'Manage Certificates',
        'view_logs' => 'View Debug Logs',
        'view_field_mapper' => 'Field Mapper',
        'view_metadata' => 'View SAML2 Metadata',
        'test_connection' => 'Test Connection',
        'save' => 'Save Configuration',
    ],
    
    'messages' => [
        'config_saved' => 'SAML2 configuration saved successfully',
        'certificates_generated' => 'Certificates generated successfully',
        'connection_tested' => 'Connection tested successfully',
        'debug_enabled' => 'Debug mode enabled',
        'debug_disabled' => 'Debug mode disabled',
    ],
    
    'buttons' => [
        'login_with_okta' => 'Login with Okta',
        'login_with_saml2' => 'Login with SAML2',
        'login_with_microsoft' => 'Login with Microsoft',
        'login_with_google' => 'Login with Google',
        'login_with_auth0' => 'Login with Auth0',
    ],
    
    'icons' => [
        'okta' => 'Okta',
        'microsoft' => 'Microsoft',
        'google' => 'Google',
        'auth0' => 'Auth0',
        'heroicons' => 'Heroicons',
        'shield_check' => 'Shield Check',
        'lock_closed' => 'Lock Closed',
        'key' => 'Key',
        'rocket_launch' => 'Rocket Launch',
        'user' => 'User',
        'login' => 'Login',
        'identification' => 'Identification',
        'finger_print' => 'Finger Print',
    ],
];
