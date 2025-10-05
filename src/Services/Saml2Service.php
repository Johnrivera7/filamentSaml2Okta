<?php

namespace JohnRiveraGonzalez\Saml2Okta\Services;

use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class Saml2Service
{
    /**
     * Obtener la configuración activa y configurar Socialite
     */
    public function configureSocialite(): ?Saml2OktaConfig
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if (!$config) {
            Log::warning('No hay configuración SAML2 activa');
            return null;
        }

        try {
            // Configurar Socialite con la configuración activa
            config(['services.saml2' => $config->toSocialiteConfig()]);
            
            Log::info('SAML2 configurado correctamente', [
                'config_name' => $config->name,
                'client_id' => $config->client_id
            ]);
            
            return $config;
        } catch (\Exception $e) {
            Log::error('Error configurando SAML2: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Iniciar el flujo SAML2
     */
    public function redirect(): \Illuminate\Http\RedirectResponse
    {
        $config = $this->configureSocialite();
        
        if (!$config) {
            return redirect('/admin/login')->with('error', 'Configuración SAML2 no disponible');
        }

        try {
            $redirectUrl = Socialite::driver('saml2')->redirect();
            Log::info('SAML2 redirect iniciado correctamente');
            return $redirectUrl;
        } catch (\Exception $e) {
            Log::error('Error en SAML2 redirect: ' . $e->getMessage());
            return redirect('/admin/login')->with('error', 'Error al iniciar sesión con Okta');
        }
    }

    /**
     * Procesar el callback SAML2
     */
    public function handleCallback(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $config = $this->configureSocialite();
        
        if (!$config) {
            return redirect('/admin/login')->with('error', 'Configuración SAML2 no disponible');
        }

        try {
            Log::info('SAML2 callback recibido', [
                'has_saml_response' => $request->has('SAMLResponse'),
                'has_saml_request' => $request->has('SAMLRequest')
            ]);

            // Verificar si es una SAML Request (debe redirigir a Okta)
            if ($request->has('SAMLRequest')) {
                Log::info('SAML2 request detectado, redirigiendo a Okta SSO');
                return redirect($config->idp_sso_url);
            }

            // Verificar si es una SAML Response (usuario regresando de Okta)
            if ($request->has('SAMLResponse')) {
                Log::info('SAML2 response detectado, procesando datos del usuario');
                $samlUser = Socialite::driver('saml2')->user();

                Log::info('Datos del usuario SAML2:', [
                    'id' => $samlUser->getId(),
                    'email' => $samlUser->getEmail(),
                    'name' => $samlUser->getName()
                ]);

                // Aquí puedes implementar la lógica para crear/actualizar el usuario
                // y autenticarlo en tu aplicación
                
                return redirect('/admin')->with('success', 'Autenticación exitosa');
            }

            Log::warning('SAML2 callback sin SAMLRequest o SAMLResponse');
            return redirect('/admin/login')->with('error', 'Respuesta SAML2 inválida');

        } catch (\Exception $e) {
            Log::error('Error en SAML2 callback: ' . $e->getMessage());
            return redirect('/admin/login')->with('error', 'Error al procesar la respuesta de Okta');
        }
    }

    /**
     * Verificar si SAML2 está configurado y activo
     */
    public function isConfigured(): bool
    {
        return Saml2OktaConfig::where('is_active', true)->exists();
    }

    /**
     * Obtener la configuración del botón
     */
    public function getButtonConfig(): ?array
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if (!$config) {
            return null;
        }

        return [
            'label' => $config->button_label,
            'icon' => $config->button_icon,
            'url' => route('saml2.login'),
        ];
    }
}
