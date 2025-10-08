<?php

namespace JohnRiveraGonzalez\Saml2Okta\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SamlDebugService
{
    protected string $storagePath = 'saml2-okta/debug';
    
    public function __construct()
    {
        // Crear directorio de debug si no existe
        Storage::makeDirectory($this->storagePath);
    }
    
    public function logSamlRequest(array $data, string $type = 'request'): void
    {
        // Verificar si el modo debug está activo
        if (!$this->isDebugModeActive()) {
            return;
        }

        $logData = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'data' => $data,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        // Log a archivo de Laravel con prefijo SAML2
        Log::info('[SAML2] ' . Str::title($type), $logData);
        
        // Guardar en storage para análisis posterior
        $filename = $this->storagePath . '/' . now()->format('Y-m-d') . '_' . $type . '_' . Str::random(8) . '.json';
        Storage::put($filename, json_encode($logData, JSON_PRETTY_PRINT));
    }
    
    public function logSamlResponse(array $data, string $type = 'response'): void
    {
        // Verificar si el modo debug está activo
        if (!$this->isDebugModeActive()) {
            return;
        }

        $logData = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'data' => $data,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        // Log a archivo de Laravel con prefijo SAML2
        Log::info('[SAML2] ' . Str::title($type), $logData);
        
        // Guardar en storage para análisis posterior
        $filename = $this->storagePath . '/' . now()->format('Y-m-d') . '_' . $type . '_' . Str::random(8) . '.json';
        Storage::put($filename, json_encode($logData, JSON_PRETTY_PRINT));
    }
    
    public function logSamlUser(array $samlUser, array $mappedUser = []): void
    {
        // Verificar si el modo debug está activo
        if (!$this->isDebugModeActive()) {
            return;
        }

        $logData = [
            'timestamp' => now()->toISOString(),
            'type' => 'user_mapping',
            'saml_user' => $samlUser,
            'mapped_user' => $mappedUser,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        // Log a archivo de Laravel con prefijo SAML2
        Log::info('[SAML2] User Mapping', $logData);
        
        // Guardar en storage para análisis posterior
        $filename = $this->storagePath . '/' . now()->format('Y-m-d') . '_user_mapping_' . Str::random(8) . '.json';
        Storage::put($filename, json_encode($logData, JSON_PRETTY_PRINT));
    }
    
    public function logSamlError(string $error, array $context = []): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'type' => 'error',
            'error' => $error,
            'context' => $context,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        // Log a archivo de Laravel con prefijo SAML2
        Log::error('[SAML2] Error', $logData);
        
        // Guardar en storage para análisis posterior
        $filename = $this->storagePath . '/' . now()->format('Y-m-d') . '_error_' . Str::random(8) . '.json';
        Storage::put($filename, json_encode($logData, JSON_PRETTY_PRINT));
    }
    
    public function getDebugLogs(string $date = null): array
    {
        $date = $date ?: now()->format('Y-m-d');
        $pattern = $this->storagePath . '/' . $date . '_*.json';
        
        $files = File::glob(storage_path('app/' . $pattern));
        $logs = [];
        
        foreach ($files as $file) {
            $content = Storage::get($file);
            $logData = json_decode($content, true);
            
            if ($logData) {
                $logs[] = [
                    'file' => basename($file),
                    'timestamp' => $logData['timestamp'],
                    'type' => $logData['type'],
                    'data' => $logData,
                ];
            }
        }
        
        // Ordenar por timestamp descendente
        usort($logs, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return $logs;
    }
    
    public function clearDebugLogs(string $date = null): bool
    {
        $date = $date ?: now()->format('Y-m-d');
        $pattern = $this->storagePath . '/' . $date . '_*.json';
        
        $files = File::glob(storage_path('app/' . $pattern));
        $deleted = 0;
        
        foreach ($files as $file) {
            if (Storage::delete($file)) {
                $deleted++;
            }
        }
        
        return $deleted > 0;
    }
    
    public function getAvailableDates(): array
    {
        $pattern = $this->storagePath . '/*.json';
        $files = File::glob(storage_path('app/' . $pattern));
        $dates = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/^(\d{4}-\d{2}-\d{2})_/', $filename, $matches)) {
                $dates[] = $matches[1];
            }
        }
        
        $dates = array_unique($dates);
        rsort($dates);
        
        return $dates;
    }
    
    public function analyzeSamlFields(array $samlUser): array
    {
        $analysis = [
            'available_fields' => array_keys($samlUser),
            'field_types' => [],
            'suggested_mappings' => [],
        ];
        
        foreach ($samlUser as $key => $value) {
            $analysis['field_types'][$key] = gettype($value);
            
            // Sugerir mapeos basados en nombres comunes
            $suggestedMapping = $this->suggestFieldMapping($key, $value);
            if ($suggestedMapping) {
                $analysis['suggested_mappings'][$key] = $suggestedMapping;
            }
        }
        
        return $analysis;
    }
    
    protected function suggestFieldMapping(string $key, $value): ?string
    {
        $key = strtolower($key);
        
        $mappings = [
            'email' => 'email',
            'mail' => 'email',
            'userprincipalname' => 'email',
            'name' => 'name',
            'displayname' => 'name',
            'givenname' => 'firstname',
            'firstname' => 'firstname',
            'sn' => 'lastname',
            'surname' => 'lastname',
            'lastname' => 'lastname',
            'username' => 'username',
            'uid' => 'username',
            'sAMAccountName' => 'username',
            'objectguid' => 'okta_id',
            'id' => 'okta_id',
            'userid' => 'okta_id',
        ];
        
        return $mappings[$key] ?? null;
    }

    protected function isDebugModeActive(): bool
    {
        try {
            $config = \JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig::getActiveConfig();
            return $config && $config->debug_mode;
        } catch (\Exception $e) {
            return false;
        }
    }
}
