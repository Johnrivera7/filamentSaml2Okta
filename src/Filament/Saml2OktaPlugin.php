<?php

namespace JohnRiveraGonzalez\Saml2Okta\Filament;

use Filament\Panel;
use Filament\Contracts\Plugin;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;

class Saml2OktaPlugin implements Plugin
{
    protected ?Saml2OktaConfig $config = null;

    public function getId(): string
    {
        return 'saml2-okta';
    }

    public function register(Panel $panel): void
    {
        // Registrar el plugin en el panel
        $panel->authGuard('web');
        
        // Registrar las rutas SAML2
        $this->registerRoutes($panel);
    }

    public function boot(Panel $panel): void
    {
        // Obtener la configuración activa
        $this->config = Saml2OktaConfig::getActiveConfig();
        
        if (!$this->config || !$this->config->is_active) {
            return;
        }

        // Extender la página de login con el botón SAML2
        $this->extendLoginPage($panel);
    }

    protected function registerRoutes(Panel $panel): void
    {
        $panel->routes(function () {
            // Las rutas ya están registradas en el ServiceProvider
        });
    }

    protected function extendLoginPage(Panel $panel): void
    {
        // Extender la página de login para incluir el botón SAML2
        $panel->renderHook('panels::auth.login.form.after', function () {
            if (!$this->config || !$this->config->is_active) {
                return '';
            }

            $buttonLabel = $this->config->button_label ?? 'Iniciar sesión con Okta';
            $loginUrl = route('saml2.login');

            return view('saml2-okta::login-button', [
                'buttonLabel' => $buttonLabel,
                'loginUrl' => $loginUrl,
            ]);
        });
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
