<?php

namespace JohnRiveraGonzalez\Saml2Okta\Pages;

use Filament\Pages\SettingsPage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;
use JohnRiveraGonzalez\Saml2Okta\Services\CertificateService;

class Saml2OktaSettingsPage extends SettingsPage
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'saml2-okta::settings';

    protected static ?string $title = 'Configuración SAML2 Okta';

    protected static ?string $navigationLabel = 'Configuración SAML2';

    protected static ?string $navigationGroup = 'SAML2';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $config = Saml2OktaConfig::where('is_active', true)->first();
        
        if ($config) {
            $this->form->fill($config->toArray());
        }
        
        // Establecer valores automáticos
        $this->data['sp_entity_id'] = url('/saml2/metadata');
        $this->data['callback_url'] = url('/saml2/callback');
    }
    
    public static function getSettings(): string
    {
        return 'saml2-okta';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración Básica')
                    ->description('Configuración básica para la autenticación SAML2 con Okta')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre de la configuración')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('client_id')
                            ->label('Client ID')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('client_secret')
                            ->label('Client Secret')
                            ->required()
                            ->maxLength(255)
                            ->password(),
                        
                        TextInput::make('callback_url')
                            ->label('Callback URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->default(fn () => url('/saml2/callback'))
                            ->helperText('URL de callback para SAML2 (se genera automáticamente)')
                            ->readOnly(),
                    ])
                    ->columns(2),

                Section::make('Configuración del Proveedor de Identidad (Okta)')
                    ->description('Configuración específica de Okta como proveedor de identidad')
                    ->schema([
                        TextInput::make('idp_entity_id')
                            ->label('IDP Entity ID')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Entity ID de Okta. Ejemplo: http://www.okta.com/EXK123456'),
                        
                        TextInput::make('idp_sso_url')
                            ->label('IDP SSO URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL de inicio de sesión único de Okta'),
                        
                        TextInput::make('idp_slo_url')
                            ->label('IDP SLO URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL de cierre de sesión único de Okta (opcional)'),
                        
                        TextInput::make('idp_metadata_url')
                            ->label('IDP Metadata URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL de metadatos de Okta (opcional)'),
                        
                        Textarea::make('idp_x509_cert')
                            ->label('IDP X.509 Certificate')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull()
                            ->helperText('Certificado X.509 de Okta (incluir BEGIN y END CERTIFICATE)'),
                    ])
                    ->columns(2),

                Section::make('Configuración del Proveedor de Servicio (Tu aplicación)')
                    ->description('Configuración de tu aplicación como proveedor de servicio')
                    ->schema([
                        TextInput::make('sp_entity_id')
                            ->label('SP Entity ID')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => url('/saml2/metadata'))
                            ->helperText('Entity ID de tu aplicación (se genera automáticamente)')
                            ->readOnly(),
                        
                        Textarea::make('sp_x509_cert')
                            ->label('SP X.509 Certificate')
                            ->required()
                            ->rows(10)
                            ->helperText('Certificado X.509 de tu aplicación'),
                        
                        Textarea::make('sp_private_key')
                            ->label('SP Private Key')
                            ->required()
                            ->rows(10)
                            ->helperText('Clave privada de tu aplicación'),
                        
                        // Botones de gestión de certificados
                        Actions::make([
                            FormAction::make('generateCertificates')
                                ->label('Generar Certificados')
                                ->icon('heroicon-o-plus')
                                ->color('success')
                                ->action('generateCertificates'),
                            
                            FormAction::make('regenerateCertificates')
                                ->label('Regenerar Certificados')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action('regenerateCertificates'),
                        ])
                        ->columns(2),
                    ])
                    ->columns(1),

                Section::make('Configuración de Usuarios')
                    ->description('Configuración del manejo de usuarios SAML2')
                    ->schema([
                        Toggle::make('auto_create_users')
                            ->label('Crear usuarios automáticamente')
                            ->default(true)
                            ->helperText('Crear usuarios automáticamente cuando se autentican por primera vez'),
                        
                        Toggle::make('auto_update_users')
                            ->label('Actualizar usuarios existentes')
                            ->default(true)
                            ->helperText('Actualizar datos de usuarios existentes con información de Okta'),
                        
                        TextInput::make('default_role')
                            ->label('Rol por defecto')
                            ->default('user')
                            ->maxLength(255)
                            ->helperText('Rol que se asignará a nuevos usuarios SAML2'),
                        
                        Toggle::make('mark_as_external')
                            ->label('Marcar como usuario externo')
                            ->default(true)
                            ->helperText('Marcar usuarios SAML2 como usuarios externos'),
                        
                        TextInput::make('okta_id_field')
                            ->label('Campo para ID de Okta')
                            ->default('okta_id')
                            ->maxLength(255)
                            ->helperText('Nombre del campo donde guardar el ID de Okta (se creará automáticamente)'),
                    ])
                    ->columns(2),

                Section::make('Configuración de Debug')
                    ->description('Configuración del modo debug y logging')
                    ->schema([
                        Toggle::make('debug_mode')
                            ->label('Modo Debug')
                            ->helperText('Activar logging detallado para análisis de campos SAML')
                            ->default(false),
                        
                        Actions::make([
                            FormAction::make('viewLogs')
                                ->label('Ver Logs Debug')
                                ->icon('heroicon-o-bug-ant')
                                ->color('info')
                                ->url(fn () => '/admin/saml2-debug'),
                        ]),
                    ])
                    ->columns(1),

                Section::make('Gestión de Certificados')
                    ->description('Generar y gestionar certificados SAML2')
                    ->schema([
                        Actions::make([
                            FormAction::make('viewCertificates')
                                ->label('Gestionar Certificados')
                                ->icon('heroicon-o-key')
                                ->color('warning')
                                ->url(fn () => '/admin/saml2-certificates'),
                            
                            FormAction::make('viewMetadata')
                                ->label('Ver Metadatos SAML2')
                                ->icon('heroicon-o-document-text')
                                ->color('info')
                                ->url(fn () => '/saml2/metadata')
                                ->openUrlInNewTab(),
                        ]),
                    ])
                    ->columns(1),

                Section::make('Mapeo de Campos')
                    ->description('Configurar mapeo de campos SAML a User')
                    ->schema([
                        Actions::make([
                            FormAction::make('viewFieldMapper')
                                ->label('Mapeador de Campos')
                                ->icon('heroicon-o-map')
                                ->color('success')
                                ->url(fn () => '/admin/saml2-field-mapper'),
                        ]),
                    ])
                    ->columns(1),

                Section::make('Configuración de Mapeo de Campos')
                    ->description('Configuración del mapeo de campos SAML a User')
                    ->schema([
                        Textarea::make('field_mappings')
                            ->label('Mapeo de Campos')
                            ->rows(8)
                            ->helperText('Configuración JSON del mapeo de campos (usa el Mapeador de Campos para configurar)')
                            ->placeholder('{"email": "email", "name": "name", "givenName": "firstname"}'),
                    ])
                    ->columns(1),

                Section::make('Configuración de la Interfaz')
                    ->description('Configuración del botón de inicio de sesión')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activar autenticación SAML2')
                            ->default(true),
                        
                        TextInput::make('button_label')
                            ->label('Etiqueta del botón')
                            ->default('Iniciar sesión con Okta')
                            ->maxLength(255),
                        
                        Select::make('button_icon')
                            ->label('Icono del botón')
                            ->options([
                                // Iconos de proveedores
                                'okta' => 'Okta',
                                'microsoft' => 'Microsoft',
                                'google' => 'Google',
                                'auth0' => 'Auth0',
                                
                                // Separador
                                '---' => '--- Heroicons ---',
                                
                                // Heroicons
                                'heroicon-o-shield-check' => 'Shield Check',
                                'heroicon-o-lock-closed' => 'Lock Closed',
                                'heroicon-o-key' => 'Key',
                                'heroicon-o-rocket-launch' => 'Rocket Launch',
                                'heroicon-o-user' => 'User',
                                'heroicon-o-login' => 'Login',
                                'heroicon-o-identification' => 'Identification',
                                'heroicon-o-finger-print' => 'Finger Print',
                            ])
                            ->default('okta')
                            ->searchable()
                            ->helperText('Selecciona un icono de proveedor o Heroicon para el botón de login'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar Configuración')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
            
            Action::make('test')
                ->label('Probar Conexión')
                ->icon('heroicon-o-wifi')
                ->color('warning')
                ->action('testConnection'),
        ];
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar configuración')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Establecer valores automáticos
        $data['sp_entity_id'] = url('/saml2/metadata');
        $data['callback_url'] = url('/saml2/callback');
        
        // Asegurar que los campos booleanos tengan valores correctos
        $data['auto_create_users'] = (bool) ($data['auto_create_users'] ?? false);
        $data['auto_update_users'] = (bool) ($data['auto_update_users'] ?? false);
        $data['mark_as_external'] = (bool) ($data['mark_as_external'] ?? false);
        $data['debug_mode'] = (bool) ($data['debug_mode'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        
        // Establecer valores por defecto para campos opcionales
        $data['default_role'] = $data['default_role'] ?? 'user';
        $data['okta_id_field'] = $data['okta_id_field'] ?? 'okta_id';
        $data['field_mappings'] = $data['field_mappings'] ?? [];
        
        // Normalizar TODOS los certificados: eliminar \n literales y usar saltos reales
        $certificateFields = ['idp_x509_cert', 'sp_x509_cert', 'sp_private_key'];
        foreach ($certificateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = $this->formatCertificate($data[$field]);
            }
        }
        
        // Desactivar todas las configuraciones existentes
        Saml2OktaConfig::where('is_active', true)->update(['is_active' => false]);
        
        // Crear o actualizar la configuración
        Saml2OktaConfig::updateOrCreate(
            ['name' => $data['name']],
            $data
        );
        
        Notification::make()
            ->title('Configuración guardada')
            ->body('La configuración SAML2 Okta ha sido guardada exitosamente.')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        try {
            $data = $this->form->getState();
            
            // Validar campos requeridos
            $requiredFields = [
                'client_id' => 'Client ID',
                'idp_entity_id' => 'IDP Entity ID',
                'idp_sso_url' => 'IDP SSO URL',
                'idp_x509_cert' => 'IDP X.509 Certificate',
                'sp_x509_cert' => 'SP X.509 Certificate',
                'sp_private_key' => 'SP Private Key'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($data[$field])) {
                    $missingFields[] = $label;
                }
            }
            
            if (!empty($missingFields)) {
                Notification::make()
                    ->title('Campos requeridos faltantes')
                    ->body('Por favor completa los siguientes campos: ' . implode(', ', $missingFields))
                    ->warning()
                    ->send();
                return;
            }
            
            // Validar formato del certificado
            if (!str_contains($data['idp_x509_cert'], '-----BEGIN CERTIFICATE-----')) {
                Notification::make()
                    ->title('Certificado inválido')
                    ->body('El certificado de Okta debe incluir las líneas BEGIN y END CERTIFICATE')
                    ->warning()
                    ->send();
                return;
            }
            
            // Validar formato del certificado SP
            if (!str_contains($data['sp_x509_cert'], '-----BEGIN CERTIFICATE-----')) {
                Notification::make()
                    ->title('Certificado SP inválido')
                    ->body('El certificado SP debe incluir las líneas BEGIN y END CERTIFICATE')
                    ->warning()
                    ->send();
                return;
            }
            
            // Si llegamos aquí, la configuración parece válida
            Notification::make()
                ->title('Configuración válida')
                ->body('Todos los campos requeridos están completos y tienen el formato correcto.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error de validación')
                ->body('Error al validar la configuración: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generateCertificates(): void
    {
        if (!isset($this->data['sp_entity_id']) || empty($this->data['sp_entity_id'])) {
            Notification::make()
                ->title('Error')
                ->body('Primero debes configurar el SP Entity ID')
                ->danger()
                ->send();
            return;
        }

        try {
            $parsedUrl = parse_url($this->data['sp_entity_id']);
            $domain = $parsedUrl['host'] ?? null;
            
            if (!$domain) {
                throw new \Exception('No se pudo extraer el dominio del SP Entity ID');
            }

            $certificateService = new CertificateService();
            $result = $certificateService->generateCertificate($domain, 'SAML2 Okta Plugin');

            if ($result['success']) {
                // Actualizar los campos de certificado en el formulario
                $this->data['sp_x509_cert'] = $result['certificate'];
                $this->data['sp_private_key'] = $result['private_key'];
                
                Notification::make()
                    ->title('Certificados generados exitosamente')
                    ->body('Los certificados han sido generados y configurados automáticamente. Recuerda guardar la configuración.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error generando certificados')
                ->body('No se pudieron generar los certificados: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function showCertificate(): void
    {
        if (!empty($this->data['generated_certificate'])) {
            $this->data['generated_certificate'] = $this->data['generated_certificate'];
            Notification::make()
                ->title('Certificado mostrado')
                ->success()
                ->send();
        }
    }

    public function showPrivateKey(): void
    {
        if (!empty($this->data['generated_private_key'])) {
            $this->data['generated_private_key'] = $this->data['generated_private_key'];
            Notification::make()
                ->title('Clave privada mostrada')
                ->success()
                ->send();
        }
    }

    public function regenerateCertificates(): void
    {
        if (!isset($this->data['sp_entity_id']) || empty($this->data['sp_entity_id'])) {
            Notification::make()
                ->title('Error')
                ->body('Primero debes configurar el SP Entity ID')
                ->danger()
                ->send();
            return;
        }

        try {
            $parsedUrl = parse_url($this->data['sp_entity_id']);
            $domain = $parsedUrl['host'] ?? null;
            
            if (!$domain) {
                throw new \Exception('No se pudo extraer el dominio del SP Entity ID');
            }

            $certificateService = new CertificateService();
            $result = $certificateService->regenerateCertificate($domain, 'SAML2 Okta Plugin');

            if ($result['success']) {
                // Actualizar los campos de certificado en el formulario
                $this->data['sp_x509_cert'] = $result['certificate'];
                $this->data['sp_private_key'] = $result['private_key'];
                
                Notification::make()
                    ->title('Certificados regenerados exitosamente')
                    ->body('Los certificados han sido regenerados con nuevas claves. Recuerda guardar la configuración.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error regenerando certificados')
                ->body('No se pudieron regenerar los certificados: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Formatear certificado con saltos de línea correctos
     */
    protected function formatCertificate(string $certificate): string
    {
        // Extraer las líneas BEGIN y END si existen
        $hasBegin = str_contains($certificate, '-----BEGIN');
        $hasEnd = str_contains($certificate, '-----END');
        
        // Determinar el tipo (CERTIFICATE o PRIVATE KEY)
        $type = 'CERTIFICATE';
        if (str_contains($certificate, 'PRIVATE KEY')) {
            $type = 'PRIVATE KEY';
        }
        
        // Limpiar completamente el certificado
        $cleaned = preg_replace('/-----BEGIN.*?-----/', '', $certificate);
        $cleaned = preg_replace('/-----END.*?-----/', '', $cleaned);
        $cleaned = preg_replace('/\s+/', '', $cleaned);
        
        // Dividir en líneas de 64 caracteres
        $formatted = chunk_split($cleaned, 64, "\n");
        $formatted = trim($formatted);
        
        // Agregar headers y footers
        return "-----BEGIN {$type}-----\n{$formatted}\n-----END {$type}-----";
    }
}
