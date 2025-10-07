<?php

namespace JohnRiveraGonzalez\Saml2Okta\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use JohnRiveraGonzalez\Saml2Okta\Services\CertificateService;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;

class Saml2CertificatesPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Certificados SAML2';
    protected static ?string $title = 'Gestión de Certificados SAML2';
    protected static ?string $slug = 'saml2-certificates';
    protected static ?string $navigationGroup = 'SAML2';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'saml2-okta::certificates';

    public ?array $data = [];
    public ?string $domain = null;
    public ?string $organization = null;
    public ?array $certificateInfo = null;
    public ?string $certificateContent = null;
    public ?string $privateKeyContent = null;

    protected CertificateService $certificateService;

    public function mount(): void
    {
        $this->certificateService = new CertificateService();
        $this->loadCurrentConfig();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Generar Nuevo Certificado')
                    ->description('Genera un nuevo certificado X.509 para SAML2')
                    ->schema([
                        TextInput::make('domain')
                            ->label('Dominio')
                            ->placeholder('ejemplo.com')
                            ->required()
                            ->helperText('Dominio para el cual se generará el certificado'),
                        
                        TextInput::make('organization')
                            ->label('Organización')
                            ->default('SAML2 Okta Plugin')
                            ->required()
                            ->helperText('Nombre de la organización para el certificado'),
                        
                        Actions::make([
                            Action::make('generate')
                                ->label('Generar Certificado')
                                ->icon('heroicon-o-plus')
                                ->color('success')
                                ->action('generateCertificate'),
                            
                            Action::make('regenerate')
                                ->label('Regenerar Certificado')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action('regenerateCertificate')
                                ->visible(fn () => $this->certificateInfo !== null),
                        ]),
                    ])
                    ->columns(2),

                Section::make('Información del Certificado')
                    ->description('Detalles del certificado actual')
                    ->schema([
                        TextInput::make('certificateInfo.valid_from')
                            ->label('Válido desde')
                            ->disabled(),
                        
                        TextInput::make('certificateInfo.valid_to')
                            ->label('Válido hasta')
                            ->disabled(),
                        
                        TextInput::make('certificateInfo.serial_number')
                            ->label('Número de Serie')
                            ->disabled(),
                        
                        TextInput::make('certificateInfo.days_until_expiry')
                            ->label('Días hasta expiración')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? $state . ' días' : 'N/A'),
                    ])
                    ->visible(fn () => $this->certificateInfo !== null)
                    ->columns(2),

                Section::make('Certificado Público (para Okta)')
                    ->description('Este certificado debe ser enviado a Okta para encriptar las assertions')
                    ->schema([
                        Textarea::make('certificateContent')
                            ->label('Certificado X.509')
                            ->rows(10)
                            ->disabled()
                            ->helperText('Copia este certificado y envíalo a Okta'),
                        
                        Actions::make([
                            Action::make('copyCertificate')
                                ->label('Copiar Certificado')
                                ->icon('heroicon-o-clipboard')
                                ->action('copyCertificate'),
                            
                            Action::make('downloadCertificate')
                                ->label('Descargar Certificado')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->action('downloadCertificate'),
                        ]),
                    ])
                    ->visible(fn () => $this->certificateContent !== null)
                    ->columns(1),

                Section::make('Clave Privada (para tu aplicación)')
                    ->description('Esta clave privada se usa para desencriptar las assertions de Okta')
                    ->schema([
                        Textarea::make('privateKeyContent')
                            ->label('Clave Privada')
                            ->rows(10)
                            ->disabled()
                            ->helperText('Esta clave se configura automáticamente en tu aplicación'),
                        
                        Actions::make([
                            Action::make('copyPrivateKey')
                                ->label('Copiar Clave Privada')
                                ->icon('heroicon-o-clipboard')
                                ->action('copyPrivateKey'),
                            
                            Action::make('downloadPrivateKey')
                                ->label('Descargar Clave Privada')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->action('downloadPrivateKey'),
                        ]),
                    ])
                    ->visible(fn () => $this->privateKeyContent !== null)
                    ->columns(1),

                Section::make('Acciones')
                    ->schema([
                        Actions::make([
                            Action::make('refresh')
                                ->label('Actualizar Información')
                                ->icon('heroicon-o-arrow-path')
                                ->action('refreshCertificateInfo'),
                            
                            Action::make('delete')
                                ->label('Eliminar Certificado')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action('deleteCertificate')
                                ->visible(fn () => $this->certificateInfo !== null),
                        ]),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getViewData(): array
    {
        return [
            'certificateInfo' => $this->certificateInfo,
            'hasCertificate' => $this->certificateInfo !== null,
        ];
    }

    protected function loadCurrentConfig(): void
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if ($config && $config->sp_entity_id) {
            // Extraer dominio de la Entity ID
            $parsedUrl = parse_url($config->sp_entity_id);
            $this->domain = $parsedUrl['host'] ?? null;
            
            if ($this->domain) {
                $this->loadCertificateInfo();
            }
        }
    }

    protected function loadCertificateInfo(): void
    {
        if (!$this->domain) {
            return;
        }

        $this->certificateInfo = $this->certificateService->getCertificateInfo($this->domain);
        
        if ($this->certificateInfo) {
            $certificate = $this->certificateService->getCertificate($this->domain);
            $this->certificateContent = $certificate['certificate'] ?? null;
            $this->privateKeyContent = $certificate['private_key'] ?? null;
        }
    }

    public function generateCertificate(): void
    {
        if (!$this->domain || !$this->organization) {
            Notification::make()
                ->title('Error')
                ->body('Por favor completa el dominio y organización')
                ->danger()
                ->send();
            return;
        }

        $result = $this->certificateService->generateCertificate($this->domain, $this->organization);

        if ($result['success']) {
            $this->certificateContent = $result['certificate'];
            $this->privateKeyContent = $result['private_key'];
            $this->loadCertificateInfo();
            
            Notification::make()
                ->title('Certificado generado exitosamente')
                ->body('El certificado ha sido generado y está listo para usar')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error generando certificado')
                ->body($result['error'])
                ->danger()
                ->send();
        }
    }

    public function regenerateCertificate(): void
    {
        if (!$this->domain || !$this->organization) {
            return;
        }

        $result = $this->certificateService->regenerateCertificate($this->domain, $this->organization);

        if ($result['success']) {
            $this->certificateContent = $result['certificate'];
            $this->privateKeyContent = $result['private_key'];
            $this->loadCertificateInfo();
            
            Notification::make()
                ->title('Certificado regenerado exitosamente')
                ->body('El certificado ha sido regenerado con nuevas claves')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error regenerando certificado')
                ->body($result['error'])
                ->danger()
                ->send();
        }
    }

    public function refreshCertificateInfo(): void
    {
        $this->loadCertificateInfo();
        
        Notification::make()
            ->title('Información actualizada')
            ->success()
            ->send();
    }

    public function deleteCertificate(): void
    {
        if (!$this->domain) {
            return;
        }

        $deleted = $this->certificateService->deleteCertificate($this->domain);
        
        if ($deleted) {
            $this->certificateInfo = null;
            $this->certificateContent = null;
            $this->privateKeyContent = null;
            
            Notification::make()
                ->title('Certificado eliminado')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error eliminando certificado')
                ->body('No se pudo eliminar el certificado')
                ->danger()
                ->send();
        }
    }

    public function copyCertificate(): void
    {
        if ($this->certificateContent) {
            $this->js('navigator.clipboard.writeText(arguments[0])', $this->certificateContent);
            
            Notification::make()
                ->title('Certificado copiado al portapapeles')
                ->success()
                ->send();
        }
    }

    public function copyPrivateKey(): void
    {
        if ($this->privateKeyContent) {
            $this->js('navigator.clipboard.writeText(arguments[0])', $this->privateKeyContent);
            
            Notification::make()
                ->title('Clave privada copiada al portapapeles')
                ->success()
                ->send();
        }
    }

    public function downloadCertificate(): void
    {
        if ($this->certificateContent) {
            $filename = 'saml_certificate_' . $this->domain . '.pem';
            $this->js('downloadTextAsFile(arguments[0], arguments[1])', $this->certificateContent, $filename);
        }
    }

    public function downloadPrivateKey(): void
    {
        if ($this->privateKeyContent) {
            $filename = 'saml_private_key_' . $this->domain . '.pem';
            $this->js('downloadTextAsFile(arguments[0], arguments[1])', $this->privateKeyContent, $filename);
        }
    }

    public static function canAccess(): bool
    {
        // Permitir acceso solo a super_admin, pero ocultar del menú
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false; // Ocultar del menú de navegación
    }
}
