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
            'certificate' => $this->idp_x509_cert,
            'sp_entity_id' => $this->sp_entity_id,
            'idp_entity_id' => $this->idp_entity_id,
            'idp_sso_url' => $this->idp_sso_url,
            'idp_x509_cert' => $this->idp_x509_cert,
            'idp_slo_url' => $this->idp_slo_url,
            'sp_x509_cert' => $this->getSpCertificate(),
            'sp_certificate' => $this->getSpCertificate(), // El provider usa este campo para desencriptar
            'sp_private_key' => $this->getSpPrivateKey(),
            'stateless' => true, // SAML2 es stateless por naturaleza
            // Mapeo de atributos SAML a propiedades del User de Socialite
            'attribute_map' => [
                'email' => 'email',
                'name' => 'firstname',
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'idnumber' => 'id',
            ],
        ];
    }
    
    /**
     * Obtener el certificado SP
     */
    protected function getSpCertificate(): ?string
    {
        return $this->normalizeCertificate($this->sp_x509_cert);
    }
    
    /**
     * Obtener la clave privada SP
     */
    protected function getSpPrivateKey(): ?string
    {
        return $this->normalizeCertificate($this->sp_private_key);
    }
    
    /**
     * Normalizar certificados: limpiar y reformatear correctamente
     */
    protected function normalizeCertificate(?string $certificate): ?string
    {
        if (!$certificate) {
            return null;
        }
        
        // Eliminar todos los \n literales y saltos de línea existentes
        $certificate = str_replace(['\\n', "\n", "\r"], '', $certificate);
        
        // Eliminar espacios en blanco extra
        $certificate = trim($certificate);
        
        // Detectar el tipo de certificado
        $isPrivateKey = strpos($certificate, '-----BEGIN PRIVATE KEY-----') !== false;
        $isCertificate = strpos($certificate, '-----BEGIN CERTIFICATE-----') !== false;
        
        if (!$isPrivateKey && !$isCertificate) {
            return $certificate; // No es un certificado válido
        }
        
        // Extraer el header, body y footer
        if ($isPrivateKey) {
            $header = '-----BEGIN PRIVATE KEY-----';
            $footer = '-----END PRIVATE KEY-----';
        } else {
            $header = '-----BEGIN CERTIFICATE-----';
            $footer = '-----END CERTIFICATE-----';
        }
        
        // Extraer solo el cuerpo del certificado (sin headers)
        $body = str_replace([$header, $footer], '', $certificate);
        $body = trim($body);
        
        // Reformatear: agregar saltos de línea cada 64 caracteres
        $formatted = $header . "\n";
        $formatted .= chunk_split($body, 64, "\n");
        $formatted = rtrim($formatted) . "\n"; // Eliminar último salto extra y agregar uno solo
        $formatted .= $footer;
        
        return $formatted;
    }
}
