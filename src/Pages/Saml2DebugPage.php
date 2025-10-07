<?php

namespace JohnRiveraGonzalez\Saml2Okta\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;

class Saml2DebugPage extends Page implements HasActions
{
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';
    protected static ?string $navigationLabel = 'Debug SAML2';
    protected static ?string $title = 'Debug y Logs SAML2';
    protected static ?string $slug = 'saml2-debug';
    protected static ?string $navigationGroup = 'SAML2';
    protected static ?int $navigationSort = 3;

    public ?array $logs = [];
    public ?string $selectedLog = null;

    public function mount(): void
    {
        $this->loadLogs();
    }

    public function loadLogs(): void
    {
        $logPath = storage_path('app/saml2-okta-debug');
        
        if (!File::exists($logPath)) {
            $this->logs = [];
            return;
        }

        $files = File::files($logPath);
        $this->logs = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            $logData = json_decode($content, true);
            
            if ($logData) {
                $this->logs[] = [
                    'file' => $file->getFilename(),
                    'date' => $file->getMTime(),
                    'data' => $logData
                ];
            }
        }

        // Ordenar por fecha (más recientes primero)
        usort($this->logs, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }

    public function clearLogs(): void
    {
        $logPath = storage_path('app/saml2-okta-debug');
        
        if (File::exists($logPath)) {
            File::deleteDirectory($logPath);
            File::makeDirectory($logPath, 0755, true);
        }
        
        $this->loadLogs();
        
        Notification::make()
            ->title('Logs eliminados')
            ->body('Todos los logs de debug han sido eliminados.')
            ->success()
            ->send();
    }

    public function refreshLogs(): void
    {
        $this->loadLogs();
        
        Notification::make()
            ->title('Logs actualizados')
            ->body('Los logs han sido actualizados.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualizar Logs')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action('refreshLogs'),
            
            Action::make('clear')
                ->label('Limpiar Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action('clearLogs'),
        ];
    }

    public function getViewData(): array
    {
        return [
            'logs' => $this->logs,
            'selectedLog' => $this->selectedLog,
            'debugMode' => $this->isDebugModeActive(),
        ];
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

    protected function isDebugModeActive(): bool
    {
        $config = Saml2OktaConfig::where('is_active', true)->first();
        return $config && $config->debug_mode;
    }

    public function getView(): string
    {
        return 'saml2-okta::debug';
    }
}