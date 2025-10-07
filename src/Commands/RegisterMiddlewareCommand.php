<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterMiddlewareCommand extends Command
{
    protected $signature = 'saml2-okta:register-middleware';

    protected $description = 'Registrar middleware para inyectar botón SAML2';

    public function handle(): int
    {
        $this->info('Registrando middleware SAML2...');

        $kernelPath = app_path('Http/Kernel.php');
        
        if (!File::exists($kernelPath)) {
            $this->error('No se encontró el archivo Kernel.php');
            return self::FAILURE;
        }

        $content = File::get($kernelPath);
        
        // Verificar si ya está registrado
        if (strpos($content, 'InjectSaml2ButtonMiddleware') !== false) {
            $this->info('El middleware ya está registrado.');
            return self::SUCCESS;
        }

        // Agregar el middleware al array $middlewareGroups
        $pattern = '/(\s+)(\\\\Illuminate\\\\Routing\\\\Middleware\\\\SubstituteBindings::class,)/';
        $replacement = '$1$2' . "\n" . 
            '$1\\\\JohnRiveraGonzalez\\\\Saml2Okta\\\\Middleware\\\\InjectSaml2ButtonMiddleware::class,';
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== $content) {
            File::put($kernelPath, $newContent);
            $this->info('Middleware registrado exitosamente.');
        } else {
            $this->warn('No se pudo registrar el middleware automáticamente.');
            $this->info('Registra manualmente en app/Http/Kernel.php:');
            $this->info('\\JohnRiveraGonzalez\\Saml2Okta\\Middleware\\InjectSaml2ButtonMiddleware::class,');
        }

        return self::SUCCESS;
    }
}
