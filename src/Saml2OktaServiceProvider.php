<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use JohnRiveraGonzalez\Saml2Okta\Commands\InstallCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\ExtendUserModelCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\ExtendUserResourceCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\RegisterMiddlewareCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\UnregisterMiddlewareCommand;

class Saml2OktaServiceProvider extends ServiceProvider
{
    protected array $resources = [
        //
    ];

    protected array $pages = [
        \JohnRiveraGonzalez\Saml2Okta\Pages\Saml2OktaSettingsPage::class,
        \JohnRiveraGonzalez\Saml2Okta\Pages\Saml2CertificatesPage::class,
        \JohnRiveraGonzalez\Saml2Okta\Pages\Saml2DebugPage::class,
        \JohnRiveraGonzalez\Saml2Okta\Pages\Saml2FieldMapperPage::class,
    ];

    protected array $commands = [
        InstallCommand::class,
        ExtendUserModelCommand::class,
        ExtendUserResourceCommand::class,
        RegisterMiddlewareCommand::class,
        UnregisterMiddlewareCommand::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('saml2-okta')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations()
            ->hasTranslations();
    }

    public function boot(): void
    {
        // Registrar rutas SAML2
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        
        // Registrar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Registrar vistas
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'saml2-okta');
        
        // Publicar vista de extensión de login
        $this->publishes([
            __DIR__ . '/../resources/views/extend-login.blade.php' => resource_path('views/vendor/saml2-okta/extend-login.blade.php'),
        ], 'saml2-okta-views');
        
        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ExtendUserModelCommand::class,
                ExtendUserResourceCommand::class,
                RegisterMiddlewareCommand::class,
                UnregisterMiddlewareCommand::class,
            ]);
        }
        
        // Registrar el servicio SAML2
        $this->app->singleton(Saml2Service::class, function ($app) {
            return new Saml2Service();
        });
        
        
        // Registrar configuración de logging
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging.channels');
        
        
    }

    public function register(): void
    {
        // Registrar configuración
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2-okta.php', 'saml2-okta');
    }
}
