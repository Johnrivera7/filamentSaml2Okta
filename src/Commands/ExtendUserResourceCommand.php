<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExtendUserResourceCommand extends Command
{
    protected $signature = 'saml2-okta:extend-user-resource';
    protected $description = 'Extend the UserResource to show SAML2 authentication fields';

    public function handle()
    {
        $this->info('Extending UserResource for SAML2 authentication...');

        // Buscar el UserResource
        $userResourcePath = $this->findUserResource();
        
        if (!$userResourcePath) {
            $this->error('UserResource not found. Please ensure you have a UserResource in your app/Filament/Resources directory.');
            return 1;
        }

        // Leer el contenido actual
        $content = File::get($userResourcePath);
        
        // Verificar si ya está extendido
        if (str_contains($content, 'okta_id') || str_contains($content, 'auth_method')) {
            $this->info('UserResource already extended for SAML2 authentication.');
            return 0;
        }

        // Extender el UserResource
        $this->extendUserResource($userResourcePath, $content);
        
        $this->info('UserResource successfully extended for SAML2 authentication!');
        $this->info('Added fields: okta_id, auth_method');
        
        return 0;
    }

    private function findUserResource(): ?string
    {
        $possiblePaths = [
            app_path('Filament/Resources/UserResource.php'),
            app_path('Filament/Resources/User/UserResource.php'),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function extendUserResource(string $path, string $content): void
    {
        // Agregar campos al formulario
        $content = $this->addFormFields($content);
        
        // Agregar columnas a la tabla
        $content = $this->addTableColumns($content);
        
        // Guardar el archivo
        File::put($path, $content);
    }

    private function addFormFields(string $content): string
    {
        // Buscar la sección donde agregar los campos (después de usuario_externo)
        $pattern = '/Forms\\\\Components\\\\Toggle::make\(\'usuario_externo\'\)([\s\S]*?)->columnSpanFull\(\),/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $existingField = $matches[0];
            
            $newFields = '
                            Forms\\Components\\TextInput::make(\'okta_id\')
                                ->label(\'ID de Okta\')
                                ->disabled()
                                ->helperText(\'ID del usuario en Okta (solo para usuarios SAML2)\')
                                ->columnSpanFull(),

                            Forms\\Components\\TextInput::make(\'auth_method\')
                                ->label(\'Método de Autenticación\')
                                ->disabled()
                                ->helperText(\'Método utilizado para autenticarse\')
                                ->columnSpanFull(),';
            
            return $existingField . $newFields;
        }, $content);
    }

    private function addTableColumns(string $content): string
    {
        // Buscar la sección de columnas de la tabla
        $pattern = '/Tables\\\\Columns\\\\TextColumn::make\(\'roles\.name\'\)([\s\S]*?)->badge\(\),/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $existingColumn = $matches[0];
            
            $newColumn = '
                Tables\\Columns\\TextColumn::make(\'auth_method\')->label(\'Método Auth\')
                    ->formatStateUsing(fn($state): string => $state ? Str::headline($state) : \'Local\')
                    ->colors([
                        \'success\' => \'local\',
                        \'warning\' => \'saml2_okta\',
                    ])
                    ->badge(),';
            
            return $existingColumn . $newColumn;
        }, $content);
    }
}

