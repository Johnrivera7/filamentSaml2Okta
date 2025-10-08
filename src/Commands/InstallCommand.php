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

        $this->info('✅ Plugin SAML2 Okta instalado exitosamente!');
        $this->newLine();
        $this->info('Los campos SAML2 han sido agregados automáticamente al modelo User y UserResource.');
        $this->info('El botón de login SAML2 se inyecta automáticamente usando Filament Render Hooks.');
        $this->newLine();
        $this->info('📋 Próximos pasos:');
        $this->info('1. Registrar el plugin en app/Providers/Filament/AdminPanelProvider.php');
        $this->info('   ->plugins([Saml2OktaPlugin::make()])');
        $this->info('2. Configurar SAML2 desde el panel: /admin/saml2-settings');
        $this->info('3. Generar certificados desde: /admin/saml2-certificates');

        return self::SUCCESS;
    }
}
