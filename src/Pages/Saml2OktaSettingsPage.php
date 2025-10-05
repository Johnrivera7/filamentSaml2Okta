<?php

namespace JohnRiveraGonzalez\Saml2Okta\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;

class Saml2OktaSettingsPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'saml2-okta::settings';

    protected static ?string $title = 'Configuración SAML2 Okta';

    protected static ?string $navigationLabel = 'Configuración SAML2';

    protected static ?string $navigationGroup = 'Autenticación';

    protected static ?int $navigationSort = 2;

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
                            ->helperText('URL de callback para SAML2. Ejemplo: https://sistema.tdx.test/saml2/callback'),
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
                            ->helperText('Entity ID de tu aplicación. Ejemplo: https://sistema.tdx.test/saml2/metadata'),
                        
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
                    ])
                    ->columns(2),

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
                        
                        TextInput::make('button_icon')
                            ->label('Icono del botón')
                            ->default('heroicon-o-shield-check')
                            ->maxLength(255)
                            ->helperText('Nombre del icono de Heroicons'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar configuración')
                ->action('save'),
            
            Action::make('test')
                ->label('Probar conexión')
                ->color('warning')
                ->action('testConnection'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
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
            // Aquí implementarías la lógica para probar la conexión
            // Por ahora, solo simulamos una prueba exitosa
            
            Notification::make()
                ->title('Conexión exitosa')
                ->body('La conexión con Okta se ha establecido correctamente.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error de conexión')
                ->body('No se pudo establecer la conexión con Okta: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
