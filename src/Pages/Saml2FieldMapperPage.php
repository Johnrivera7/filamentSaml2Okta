<?php

namespace JohnRiveraGonzalez\Saml2Okta\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;
use JohnRiveraGonzalez\Saml2Okta\Services\SamlDebugService;

class Saml2FieldMapperPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Mapeador de Campos';
    protected static ?string $title = 'Mapeador de Campos SAML2';
    protected static ?string $slug = 'saml2-field-mapper';
    protected static ?string $navigationGroup = 'SAML2';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'saml2-okta::field-mapper';

    public ?array $data = [];
    public ?array $fieldMappings = [];
    public ?array $sampleSamlData = [];
    public ?array $userFields = [];
    public ?string $selectedDate = null;

    protected SamlDebugService $debugService;

    public function mount(): void
    {
        $this->debugService = new SamlDebugService();
        $this->loadConfiguration();
        $this->loadUserFields();
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadSampleData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración de Mapeo')
                    ->description('Configura cómo se mapean los campos de Okta a tu modelo User')
                    ->schema([
                        Toggle::make('debug_mode')
                            ->label('Modo Debug')
                            ->helperText('Activa el logging detallado para analizar campos SAML')
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->saveDebugMode()),
                    ])
                    ->columns(1),

                Section::make('Campos Disponibles')
                    ->description('Campos disponibles en tu modelo User')
                    ->schema([
                        Textarea::make('userFields')
                            ->label('Campos del Modelo User')
                            ->rows(8)
                            ->disabled()
                            ->helperText('Estos son los campos disponibles en tu modelo User'),
                    ])
                    ->columns(1),

                Section::make('Datos de Ejemplo SAML')
                    ->description('Datos de ejemplo de Okta para mapear')
                    ->schema([
                        Select::make('selectedDate')
                            ->label('Fecha de Logs')
                            ->options($this->getAvailableDates())
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->loadSampleData()),
                        
                        Textarea::make('sampleSamlData')
                            ->label('Datos SAML de Ejemplo')
                            ->rows(10)
                            ->disabled()
                            ->helperText('Datos reales de Okta para configurar el mapeo'),
                    ])
                    ->columns(1),

                Section::make('Mapeo de Campos')
                    ->description('Configura el mapeo entre campos de Okta y tu modelo User')
                    ->schema([
                        Repeater::make('fieldMappings')
                            ->label('Mapeos de Campos')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('saml_field')
                                            ->label('Campo SAML (Okta)')
                                            ->placeholder('ej: email, name, givenName')
                                            ->required()
                                            ->helperText('Nombre del campo que envía Okta'),
                                        
                                        Select::make('user_field')
                                            ->label('Campo User (Laravel)')
                                            ->options($this->getUserFieldOptions())
                                            ->required()
                                            ->searchable()
                                            ->helperText('Campo en tu modelo User'),
                                        
                                        Toggle::make('required')
                                            ->label('Requerido')
                                            ->helperText('¿Es este campo obligatorio?'),
                                    ]),
                                
                                TextInput::make('default_value')
                                    ->label('Valor por Defecto')
                                    ->placeholder('Valor por defecto si el campo SAML está vacío')
                                    ->helperText('Valor que se usará si el campo SAML no tiene valor'),
                                
                                Textarea::make('transformation')
                                    ->label('Transformación')
                                    ->placeholder('ej: strtoupper($value), trim($value)')
                                    ->rows(2)
                                    ->helperText('Función PHP para transformar el valor (opcional)'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Mapeo')
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                    ])
                    ->columns(1),

                Section::make('Acciones')
                    ->schema([
                        Actions::make([
                            Action::make('loadFromLogs')
                                ->label('Cargar desde Logs')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->action('loadFromLogs')
                                ->visible(fn () => !empty($this->sampleSamlData)),
                            
                            Action::make('testMapping')
                                ->label('Probar Mapeo')
                                ->icon('heroicon-o-beaker')
                                ->action('testMapping')
                                ->visible(fn () => !empty($this->fieldMappings)),
                            
                            Action::make('save')
                                ->label('Guardar Mapeo')
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->action('save'),
                            
                            Action::make('reset')
                                ->label('Resetear')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action('reset'),
                        ]),
                    ])
                    ->columns(4),
            ])
            ->statePath('data');
    }

    protected function getViewData(): array
    {
        return [
            'fieldMappings' => $this->fieldMappings,
            'sampleSamlData' => $this->sampleSamlData,
            'userFields' => $this->userFields,
        ];
    }

    protected function loadConfiguration(): void
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if ($config) {
            $this->data['debug_mode'] = $config->debug_mode ?? false;
            $this->fieldMappings = $config->field_mappings ?? [];
        }
    }

    protected function loadUserFields(): void
    {
        // Obtener campos del modelo User
        $user = new \App\Models\User();
        $fillable = $user->getFillable();
        
        // Agregar campos adicionales comunes
        $additionalFields = [
            'id', 'created_at', 'updated_at', 'email_verified_at',
            'okta_id', 'auth_method', 'usuario_externo'
        ];
        
        $this->userFields = array_merge($fillable, $additionalFields);
        $this->data['userFields'] = implode(', ', $this->userFields);
    }

    protected function getUserFieldOptions(): array
    {
        return array_combine($this->userFields, $this->userFields);
    }

    protected function getAvailableDates(): array
    {
        $dates = $this->debugService->getAvailableDates();
        return array_combine($dates, $dates);
    }

    protected function loadSampleData(): void
    {
        if (!$this->selectedDate) {
            return;
        }

        $logs = $this->debugService->getDebugLogs($this->selectedDate);
        
        // Buscar un log con datos de usuario SAML
        foreach ($logs as $log) {
            if (isset($log['data']['saml_user']) && is_array($log['data']['saml_user'])) {
                $this->sampleSamlData = $log['data']['saml_user'];
                $this->data['sampleSamlData'] = json_encode($this->sampleSamlData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        
        // Si no hay datos del día seleccionado, buscar en los últimos 7 días
        if (empty($this->sampleSamlData)) {
            for ($i = 1; $i <= 7; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                $logs = $this->debugService->getDebugLogs($date);
                
                foreach ($logs as $log) {
                    if (isset($log['data']['saml_user']) && is_array($log['data']['saml_user'])) {
                        $this->sampleSamlData = $log['data']['saml_user'];
                        $this->data['sampleSamlData'] = json_encode($this->sampleSamlData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        $this->selectedDate = $date; // Actualizar la fecha seleccionada
                        break 2; // Salir de ambos bucles
                    }
                }
            }
        }
    }

    public function loadFromLogs(): void
    {
        if (empty($this->sampleSamlData)) {
            Notification::make()
                ->title('No hay datos')
                ->body('No se encontraron datos SAML en los logs')
                ->warning()
                ->send();
            return;
        }

        // Generar mapeos automáticos basados en los datos
        $suggestedMappings = $this->debugService->analyzeSamlFields($this->sampleSamlData);
        
        $mappings = [];
        foreach ($suggestedMappings['suggested_mappings'] as $samlField => $userField) {
            $mappings[] = [
                'saml_field' => $samlField,
                'user_field' => $userField,
                'required' => in_array($userField, ['email', 'name']),
                'default_value' => '',
                'transformation' => '',
            ];
        }

        $this->fieldMappings = $mappings;
        $this->data['fieldMappings'] = $mappings;

        Notification::make()
            ->title('Mapeos cargados')
            ->body('Se han generado mapeos automáticos basados en los logs')
            ->success()
            ->send();
    }

    public function testMapping(): void
    {
        if (empty($this->fieldMappings) || empty($this->sampleSamlData)) {
            Notification::make()
                ->title('Datos insuficientes')
                ->body('Necesitas configurar mapeos y tener datos de ejemplo')
                ->warning()
                ->send();
            return;
        }

        $mappedData = [];
        foreach ($this->fieldMappings as $mapping) {
            $samlField = $mapping['saml_field'];
            $userField = $mapping['user_field'];
            $defaultValue = $mapping['default_value'] ?? '';
            $transformation = $mapping['transformation'] ?? '';

            $value = $this->sampleSamlData[$samlField] ?? $defaultValue;

            // Aplicar transformación si existe
            if ($transformation && $value) {
                try {
                    $value = eval("return $transformation;");
                } catch (\Exception $e) {
                    $value = $this->sampleSamlData[$samlField] ?? $defaultValue;
                }
            }

            $mappedData[$userField] = $value;
        }

        $this->data['testResult'] = json_encode($mappedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        Notification::make()
            ->title('Mapeo probado')
            ->body('Revisa el resultado del mapeo en la sección de prueba')
            ->success()
            ->send();
    }

    public function save(): void
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if (!$config) {
            Notification::make()
                ->title('Error')
                ->body('No hay configuración SAML2 activa')
                ->danger()
                ->send();
            return;
        }

        $config->update([
            'field_mappings' => $this->fieldMappings,
            'debug_mode' => $this->data['debug_mode'] ?? false,
        ]);

        Notification::make()
            ->title('Configuración guardada')
            ->body('Los mapeos de campos han sido guardados')
            ->success()
            ->send();
    }

    public function saveDebugMode(): void
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if ($config) {
            $config->update([
                'debug_mode' => $this->data['debug_mode'] ?? false,
            ]);
        }
    }

    public function reset(...$properties): void
    {
        $this->fieldMappings = [];
        $this->data['fieldMappings'] = [];
        
        Notification::make()
            ->title('Mapeos reseteados')
            ->success()
            ->send();
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
