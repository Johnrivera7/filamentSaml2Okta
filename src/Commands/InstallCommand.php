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

        $this->info('Plugin SAML2 Okta instalado exitosamente!');
        $this->info('Puedes configurar SAML2 desde el panel de administración.');

        return self::SUCCESS;
    }
}
