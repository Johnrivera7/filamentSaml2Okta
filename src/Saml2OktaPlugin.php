<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2OktaSettingsPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2DebugPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2CertificatesPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2FieldMapperPage;

class Saml2OktaPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'saml2-okta';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                Saml2OktaSettingsPage::class,
                // Las otras páginas están ocultas del menú pero accesibles via botones
                Saml2DebugPage::class,
                Saml2CertificatesPage::class,
                Saml2FieldMapperPage::class,
            ]);
        
        // Registrar el render hook aquí en lugar de en boot()
        \Log::info('SAML2 Plugin - Register method called');
        
        // Obtener la configuración activa
        $config = \JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig::getActiveConfig();
        
        \Log::info('SAML2 Plugin - Config found: ' . ($config ? 'true' : 'false'));
        if ($config) {
            \Log::info('SAML2 Plugin - is_active: ' . ($config->is_active ? 'true' : 'false'));
        }
        
        if ($config && $config->is_active) {
            \Log::info('SAML2 Plugin - Extending login page with SAML2 button');
            // Extender la página de login con el botón SAML2
            $this->extendLoginPage($panel, $config);
        }
    }

    public function boot(Panel $panel): void
    {
        // Boot method - se llama después de register
    }

    protected function extendLoginPage(Panel $panel, $config): void
    {
        // Verificar si ya se registró el render hook para evitar duplicados
        static $hookRegistered = false;
        if ($hookRegistered) {
            \Log::info('SAML2 Render Hook - Already registered, skipping');
            return;
        }
        
        \Log::info('SAML2 Render Hook - Registering login button hook');
        $hookRegistered = true;
        
        // Extender la página de login para incluir el botón SAML2
        $panel->renderHook('panels::auth.login.form.after', function () use ($config) {
            \Log::info('SAML2 Render Hook - Executing login button injection');
            \Log::info('SAML2 Render Hook - Config button_label: ' . $config->button_label);
            
            $buttonLabel = $config->button_label ?? 'Iniciar sesión con Okta';
            $loginUrl = route('saml2.login');

            \Log::info('SAML2 Render Hook - Generated login URL: ' . $loginUrl);

            return view('saml2-okta::login-button', [
                'buttonLabel' => $buttonLabel,
                'buttonIcon' => $config->button_icon ?? 'rocket-launch',
                'loginUrl' => $loginUrl,
            ]);
        });
    }
}
