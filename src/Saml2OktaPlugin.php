<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JohnRiveraGonzalez\Saml2Okta\Pages\Saml2OktaSettingsPage;

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
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
