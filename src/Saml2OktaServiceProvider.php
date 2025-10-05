<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use JohnRiveraGonzalez\Saml2Okta\Commands\InstallCommand;

class Saml2OktaServiceProvider extends ServiceProvider
{
    protected array $resources = [
        //
    ];

    protected array $pages = [
        //
    ];

    protected array $commands = [
        InstallCommand::class,
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
        
        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
        
        // Registrar el servicio SAML2
        $this->app->singleton(Saml2Service::class, function ($app) {
            return new Saml2Service();
        });
    }

    public function register(): void
    {
        // Registrar configuración
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2-okta.php', 'saml2-okta');
    }
}
