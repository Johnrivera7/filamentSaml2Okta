<?php

namespace JohnRiveraGonzalez\Saml2Okta\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExtendUserModelCommand extends Command
{
    protected $signature = 'saml2-okta:extend-user-model';
    protected $description = 'Extend the User model to support SAML2 authentication fields';

    public function handle()
    {
        $this->info('Extending User model for SAML2 authentication...');

        // Buscar el modelo User
        $userModelPath = $this->findUserModel();
        
        if (!$userModelPath) {
            $this->error('User model not found. Please ensure you have a User model in your app/Models directory.');
            return 1;
        }

        // Leer el contenido actual del modelo
        $content = File::get($userModelPath);
        
        // Verificar si ya está extendido
        if (str_contains($content, 'okta_id') || str_contains($content, 'auth_method')) {
            $this->info('User model already extended for SAML2 authentication.');
            return 0;
        }

        // Extender el modelo
        $this->extendUserModel($userModelPath, $content);
        
        $this->info('User model successfully extended for SAML2 authentication!');
        $this->info('Added fields: okta_id, auth_method');
        
        return 0;
    }

    private function findUserModel(): ?string
    {
        $possiblePaths = [
            app_path('Models/User.php'),
            app_path('User.php'),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function extendUserModel(string $path, string $content): void
    {
        // Agregar campos al fillable array
        $content = $this->addToFillable($content);
        
        // Agregar métodos helper si no existen
        $content = $this->addHelperMethods($content);
        
        // Guardar el archivo
        File::put($path, $content);
    }

    private function addToFillable(string $content): string
    {
        // Buscar el array fillable y agregar los nuevos campos
        $pattern = '/protected \$fillable = \[([\s\S]*?)\];/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $fillableContent = $matches[1];
            
            // Verificar si ya contiene los campos
            if (str_contains($fillableContent, 'okta_id') || str_contains($fillableContent, 'auth_method')) {
                return $matches[0];
            }
            
            // Agregar los nuevos campos
            $newFields = "        'okta_id',\n        'auth_method',";
            
            // Insertar antes del último elemento del array
            $fillableContent = rtrim($fillableContent);
            $fillableContent = preg_replace('/\s*\'([^\']+)\',\s*$/', "        '$1',\n        " . $newFields . "\n    ];", $fillableContent);
            
            return "protected \$fillable = [" . $fillableContent;
        }, $content);
    }

    private function addHelperMethods(string $content): string
    {
        // Agregar métodos helper al final de la clase
        $helperMethods = '
    /**
     * Check if user was authenticated via SAML2
     */
    public function isSaml2User(): bool
    {
        return $this->auth_method === \'saml2_okta\';
    }

    /**
     * Get the Okta ID for this user
     */
    public function getOktaId(): ?string
    {
        return $this->okta_id;
    }

    /**
     * Get the authentication method for this user
     */
    public function getAuthMethod(): ?string
    {
        return $this->auth_method ?? \'local\';
    }';

        // Insertar antes del último }
        $content = preg_replace('/\n\s*}\s*$/', $helperMethods . "\n}\n", $content);
        
        return $content;
    }
}

