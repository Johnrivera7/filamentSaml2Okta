<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UnregisterMiddlewareCommand extends Command
{
    protected $signature = 'saml2-okta:unregister-middleware';

    protected $description = 'Limpiar referencias obsoletas al middleware SAML2 (ya no necesario)';

    public function handle(): int
    {
        $this->warn('⚠️  Limpiando referencias obsoletas al middleware SAML2...');
        $this->newLine();
        $this->info('Nota: Este middleware ya no es necesario. El plugin ahora usa Filament Render Hooks.');

        $kernelPath = app_path('Http/Kernel.php');
        
        if (!File::exists($kernelPath)) {
            $this->error('❌ No se encontró el archivo Kernel.php');
            return self::FAILURE;
        }

        $content = File::get($kernelPath);
        
        // Verificar si está registrado
        if (strpos($content, 'InjectSaml2ButtonMiddleware') === false) {
            $this->info('✅ El middleware obsoleto no está registrado en Kernel.php');
            return self::SUCCESS;
        }

        // Remover cualquier línea que contenga InjectSaml2ButtonMiddleware
        $lines = explode("\n", $content);
        $newLines = [];
        $removed = false;
        
        foreach ($lines as $line) {
            if (strpos($line, 'InjectSaml2ButtonMiddleware') === false) {
                $newLines[] = $line;
            } else {
                $removed = true;
                $this->warn('Removiendo: ' . trim($line));
            }
        }
        
        if ($removed) {
            $newContent = implode("\n", $newLines);
            File::put($kernelPath, $newContent);
            $this->info('✅ Middleware obsoleto removido exitosamente del Kernel.php');
            $this->newLine();
            $this->info('💡 Recuerda ejecutar:');
            $this->info('   php artisan config:clear');
            $this->info('   php artisan optimize');
        } else {
            $this->warn('⚠️  No se pudo remover el middleware automáticamente.');
            $this->info('Por favor, elimina manualmente estas líneas de app/Http/Kernel.php:');
            $this->info('   \\JohnRiveraGonzalez\\Saml2Okta\\Middleware\\InjectSaml2ButtonMiddleware::class,');
        }

        return self::SUCCESS;
    }
}
