<?php

namespace JohnRiveraGonzalez\Saml2Okta;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use JohnRiveraGonzalez\Saml2Okta\Commands\ExtendUserModelCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\ExtendUserResourceCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\InstallCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\RegisterMiddlewareCommand;
use JohnRiveraGonzalez\Saml2Okta\Commands\UnregisterMiddlewareCommand;
use JohnRiveraGonzalez\Saml2Okta\Services\Saml2Service;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Saml2\Saml2ExtendSocialite;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class Saml2OktaServiceProvider extends PackageServiceProvider
{
    public static string $name = 'saml2-okta';

    public static string $viewNamespace = 'saml2-okta';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasMigrations([
                'create_saml2_okta_configs_table',
                '2025_01_02_000002_add_okta_id_to_users_table',
            ])
            ->hasRoutes('web')
            ->hasCommands([
                InstallCommand::class,
                ExtendUserModelCommand::class,
                ExtendUserResourceCommand::class,
                RegisterMiddlewareCommand::class,
                UnregisterMiddlewareCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2-okta.php', 'saml2-okta');
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging.channels');

        $this->app->singleton(Saml2Service::class);
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views/extend-login.blade.php' => resource_path('views/vendor/saml2-okta/extend-login.blade.php'),
        ], 'saml2-okta-views');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/saml2-okta'),
        ], 'saml2-okta-translations');

        $this->app['events']->listen(
            SocialiteWasCalled::class,
            [Saml2ExtendSocialite::class, 'handle'],
        );

        $this->excludeFromCsrfVerification();
    }

    protected function excludeFromCsrfVerification(): void
    {
        $this->app->resolving(VerifyCsrfToken::class, function (VerifyCsrfToken $middleware): void {
            $middleware->except(array_merge(
                $middleware->except ?? [],
                [
                    'saml2/callback',
                    'auth/callback',
                ],
            ));
        });
    }
}
