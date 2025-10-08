<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterMiddlewareCommand extends Command
{
    protected $signature = 'saml2-okta:register-middleware';

    protected $description = '[OBSOLETO] Este comando ya no es necesario. El plugin usa Filament Render Hooks';

    public function handle(): int
    {
        $this->warn('⚠️  Este comando está obsoleto y ya no es necesario.');
        $this->newLine();
        $this->info('El plugin SAML2 Okta ahora usa Filament Render Hooks para inyectar el botón de login.');
        $this->info('No se requiere registrar ningún middleware manualmente.');
        $this->newLine();
        $this->info('✅ El botón de login se inyecta automáticamente cuando:');
        $this->info('   1. El plugin está registrado en AdminPanelProvider.php');
        $this->info('   2. La configuración SAML2 está activa en la base de datos');
        $this->newLine();
        $this->info('💡 Si tienes el middleware registrado en Kernel.php, ejecútalo para limpiarlo:');
        $this->info('   php artisan saml2-okta:unregister-middleware');

        return self::SUCCESS;
    }
}
