<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Illuminate\Support\ServiceProvider;
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
        
        // Registrar traducciones
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'saml2-okta');
        
        // Publicar vista de extensión de login
        $this->publishes([
            __DIR__ . '/../resources/views/extend-login.blade.php' => resource_path('views/vendor/saml2-okta/extend-login.blade.php'),
        ], 'saml2-okta-views');
        
        // Publicar traducciones
        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/saml2-okta'),
        ], 'saml2-okta-translations');
        
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
        
        // Registrar el driver SAML2 de Socialite
        $this->bootSocialiteDriver();
        
        // Excluir rutas SAML2 de verificación CSRF
        $this->excludeFromCsrfVerification();
        
        // Registrar configuración de logging
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging.channels');
    }
    
    /**
     * Registrar el driver SAML2 de Socialite
     */
    protected function bootSocialiteDriver(): void
    {
        // Registrar el listener de SocialiteWasCalled
        $this->app['events']->listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\Saml2\Saml2ExtendSocialite::class, 'handle']
        );
    }
    
    /**
     * Excluir rutas SAML2 de la verificación CSRF
     */
    protected function excludeFromCsrfVerification(): void
    {
        // Extender el middleware VerifyCsrfToken para excluir rutas SAML2
        $this->app->resolving(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, function ($middleware) {
            $middleware->except(array_merge(
                $middleware->except ?? [],
                [
                    'saml2/callback',
                    'auth/callback',
                ]
            ));
        });
    }

    public function register(): void
    {
        // Registrar configuración
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2-okta.php', 'saml2-okta');
        
        // Publicar configuración
        $this->publishes([
            __DIR__ . '/../config/saml2-okta.php' => config_path('saml2-okta.php'),
        ], 'saml2-okta-config');
    }
}
