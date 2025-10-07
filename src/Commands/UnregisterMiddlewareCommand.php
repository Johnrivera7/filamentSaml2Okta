<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UnregisterMiddlewareCommand extends Command
{
    protected $signature = 'saml2-okta:unregister-middleware';

    protected $description = 'Desregistrar middleware para inyectar botón SAML2';

    public function handle(): int
    {
        $this->info('Desregistrando middleware SAML2...');

        $kernelPath = app_path('Http/Kernel.php');
        
        if (!File::exists($kernelPath)) {
            $this->error('No se encontró el archivo Kernel.php');
            return self::FAILURE;
        }

        $content = File::get($kernelPath);
        
        // Verificar si está registrado
        if (strpos($content, 'InjectSaml2ButtonMiddleware') === false) {
            $this->info('El middleware no está registrado.');
            return self::SUCCESS;
        }

        // Remover el middleware del array $middlewareGroups
        $pattern = '/(\s+)(\\\\JohnRiveraGonzalez\\\\Saml2Okta\\\\Middleware\\\\InjectSaml2ButtonMiddleware::class,)/';
        $replacement = '';
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== $content) {
            File::put($kernelPath, $newContent);
            $this->info('Middleware desregistrado exitosamente.');
        } else {
            $this->warn('No se pudo desregistrar el middleware automáticamente.');
        }

        return self::SUCCESS;
    }
}
