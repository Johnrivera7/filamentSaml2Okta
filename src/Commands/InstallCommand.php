<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'saml2-okta:install';

    protected $description = 'Instalar el plugin SAML2 Okta';

    public function handle(): int
    {
        $this->info('Instalando plugin SAML2 Okta...');

        // Publicar migraciones
        $this->call('vendor:publish', [
            '--tag' => 'saml2-okta-migrations'
        ]);

        // Ejecutar migraciones
        $this->call('migrate');

        // Extender modelo User
        $this->call('saml2-okta:extend-user-model');

        // Extender UserResource
        $this->call('saml2-okta:extend-user-resource');

        // Registrar middleware
        $this->call('saml2-okta:register-middleware');

        $this->info('Plugin SAML2 Okta instalado exitosamente!');
        $this->info('Los campos SAML2 han sido agregados automáticamente al modelo User y UserResource.');
        $this->info('El middleware para el botón SAML2 ha sido registrado.');
        $this->info('Puedes configurar SAML2 desde el panel de administración.');

        return self::SUCCESS;
    }
}
