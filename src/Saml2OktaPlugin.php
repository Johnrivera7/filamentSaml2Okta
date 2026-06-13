<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2CertificatesPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2DebugPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2FieldMapperPage;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2OktaSettingsPage;

class Saml2OktaPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'saml2-okta';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            Saml2OktaSettingsPage::class,
            Saml2DebugPage::class,
            Saml2CertificatesPage::class,
            Saml2FieldMapperPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        $config = Saml2OktaConfig::getActiveConfig();

        if (! $config?->is_active) {
            return;
        }

        $panel->renderHook('panels::auth.login.form.after', function () use ($config) {
            return view('saml2-okta::login-button', [
                'buttonLabel' => $config->button_label ?? 'Iniciar sesión con Okta',
                'buttonIcon' => $config->button_icon ?? 'rocket-launch',
                'loginUrl' => route('saml2.login'),
            ]);
        });
    }
}
