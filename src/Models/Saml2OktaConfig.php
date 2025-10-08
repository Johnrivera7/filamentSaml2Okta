<?php

namespace JohnRiveraGonzalez\Saml2Okta\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saml2OktaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'callback_url',
        'idp_entity_id',
        'idp_sso_url',
        'idp_slo_url',
        'idp_metadata_url',
        'idp_x509_cert',
        'sp_entity_id',
        'sp_x509_cert',
        'sp_private_key',
        'is_active',
        'button_label',
        'button_icon',
        'auto_create_users',
        'auto_update_users',
        'default_role',
        'mark_as_external',
        'okta_id_field',
        'debug_mode',
        'field_mappings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_create_users' => 'boolean',
        'auto_update_users' => 'boolean',
        'mark_as_external' => 'boolean',
        'debug_mode' => 'boolean',
        'field_mappings' => 'array',
    ];

    protected $attributes = [
        'auto_create_users' => true,
        'auto_update_users' => true,
        'mark_as_external' => true,
        'debug_mode' => false,
        'default_role' => 'user',
        'okta_id_field' => 'okta_id',
        'is_active' => false,
        'button_label' => 'Iniciar sesión con Okta',
        'button_icon' => 'heroicon-o-shield-check',
        'field_mappings' => '[]',
    ];

    protected $hidden = [
        'client_secret',
        'sp_private_key',
    ];

    /**
     * Obtener la configuración activa
     */
    public static function getActiveConfig(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Convertir la configuración a array para Socialite
     */
    public function toSocialiteConfig(): array
    {
        return [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect' => $this->callback_url,
            'metadata' => $this->idp_metadata_url,
            'acs' => $this->callback_url,
            'entityid' => $this->idp_entity_id,
            'certificate' => $this->normalizeCertificate($this->idp_x509_cert),
            'sp_entity_id' => $this->sp_entity_id,
            'idp_entity_id' => $this->idp_entity_id,
            'idp_sso_url' => $this->idp_sso_url,
            'idp_x509_cert' => $this->normalizeCertificate($this->idp_x509_cert),
            'idp_slo_url' => $this->idp_slo_url,
            'sp_x509_cert' => $this->normalizeCertificate($this->sp_x509_cert),
            'sp_private_key' => $this->normalizeCertificate($this->sp_private_key),
            'stateless' => true, // SAML2 es stateless por naturaleza
        ];
    }
    
    /**
     * Normalizar certificados y claves privadas
     * Convierte \n escapados a saltos de línea reales
     */
    protected function normalizeCertificate(?string $certificate): ?string
    {
        if (!$certificate) {
            return null;
        }
        
        // Reemplazar \n escapados con saltos de línea reales
        $normalized = str_replace('\\n', "\n", $certificate);
        
        // Asegurar que no haya dobles saltos de línea
        $normalized = str_replace("\n\n", "\n", $normalized);
        
        return $normalized;
    }
}
