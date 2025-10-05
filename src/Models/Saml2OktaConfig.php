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
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
            'sp_x509_cert' => $this->sp_x509_cert,
            'sp_private_key' => $this->sp_private_key,
        ];
    }
}
