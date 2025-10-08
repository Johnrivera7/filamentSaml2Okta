<?php

namespace JohnRiveraGonzalez\Saml2Okta\Services;

use JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JohnRiveraGonzalez\Saml2Okta\Services\SamlDebugService;
use JohnRiveraGonzalez\Saml2Okta\Services\CertificateService;

class Saml2Service
{
    protected SamlDebugService $debugService;
    protected CertificateService $certificateService;
    
    public function __construct()
    {
        $this->debugService = new SamlDebugService();
        $this->certificateService = new CertificateService();
    }
    
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
    public function redirect(): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $config = $this->configureSocialite();

        if (!$config) {
            return redirect('/admin/login')->with('error', 'Configuración SAML2 no disponible');
        }

        try {
            return Socialite::driver('saml2')->stateless()->redirect();
        } catch (\Exception $e) {
            Log::error('Error en SAML2 redirect: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            return redirect('/admin/login')->with('error', 'Error al iniciar sesión con Okta: ' . $e->getMessage());
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
            // Debug logging mejorado
            $this->debugService->logSamlRequest([
                'has_saml_response' => $request->has('SAMLResponse'),
                'has_saml_request' => $request->has('SAMLRequest'),
                'all_params' => $request->all(),
                'headers' => $request->headers->all(),
            ], 'callback_received');

            // Verificar si es una SAML Request (debe redirigir a Okta)
            if ($request->has('SAMLRequest')) {
                $this->debugService->logSamlRequest([
                    'redirect_to' => $config->idp_sso_url,
                    'config_id' => $config->id,
                ], 'redirect_to_idp');
                
                return redirect($config->idp_sso_url);
            }

            // Verificar si es una SAML Response (usuario regresando de Okta)
            if ($request->has('SAMLResponse')) {
                try {
                    $samlUser = Socialite::driver('saml2')->stateless()->user();
                } catch (\Exception $e) {
                    $this->debugService->logSamlError('Error al obtener usuario de Socialite: ' . $e->getMessage(), [
                        'exception_class' => get_class($e),
                        'exception_code' => $e->getCode(),
                        'exception_file' => $e->getFile(),
                        'exception_line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return redirect('/admin/login')->with('error', 'Error al procesar la respuesta SAML2: ' . $e->getMessage());
                }

                // Log completo del usuario SAML
                $samlUserData = [
                    'id' => $samlUser->getId(),
                    'name' => $samlUser->getName(),
                    'email' => $samlUser->getEmail(),
                    'nickname' => $samlUser->getNickname(),
                    'avatar' => $samlUser->getAvatar(),
                    'attributes' => $samlUser->getRaw(),
                ];
                $this->debugService->logSamlUser($samlUserData);

                // Crear o actualizar usuario en la base de datos
                $user = $this->createOrUpdateUser($samlUser);
                
                if (!$user) {
                    $this->debugService->logSamlError('No se pudo crear o actualizar el usuario', [
                        'saml_user_data' => $samlUserData,
                    ]);
                    return redirect('/admin/login')->with('error', 'Error al procesar el usuario');
                }

                // Log del usuario mapeado
                $this->debugService->logSamlUser($samlUserData, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'okta_id' => $user->okta_id ?? null,
                    'auth_method' => $user->auth_method ?? null,
                ]);

                // Autenticar al usuario
                auth()->login($user);
                
                $this->debugService->logSamlResponse([
                    'user_logged_in' => true,
                    'user_id' => $user->id,
                    'redirect_to' => '/admin',
                ], 'authentication_success');
                
                return redirect('/admin')->with('success', 'Autenticación exitosa');
            }

            $this->debugService->logSamlError('SAML2 callback sin SAMLRequest o SAMLResponse', [
                'request_data' => $request->all(),
            ]);
            return redirect('/admin/login')->with('error', 'Respuesta SAML2 inválida');

        } catch (\Exception $e) {
            $this->debugService->logSamlError($e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
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

    /**
     * Crear o actualizar usuario desde datos SAML2
     */
    private function createOrUpdateUser($samlUser): ?User
    {
        try {
            $config = Saml2OktaConfig::getActiveConfig();
            if (!$config) {
                Log::error('No hay configuración SAML2 activa para crear usuario');
                return null;
            }

            $email = $samlUser->getEmail();
            $name = $samlUser->getName();
            $oktaId = $samlUser->getId();
            
            if (!$email) {
                Log::error('Usuario SAML2 sin email');
                return null;
            }

            // Buscar usuario existente por email o okta_id
            $user = User::where('email', $email)
                ->orWhere($config->okta_id_field, $oktaId)
                ->first();

            // Dividir el nombre en firstname y lastname
            $nameParts = explode(' ', $name, 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            $userData = [
                'username' => $email, // Usar email como username
                'firstname' => $firstname,
                'lastname' => $lastname,
                'password' => Hash::make(Str::random(32)), // Contraseña aleatoria
                'email_verified_at' => now(),
                'auth_method' => 'saml2_okta',
                $config->okta_id_field => $oktaId,
            ];

            // Agregar usuario_externo si está configurado
            if ($config->mark_as_external) {
                $userData['usuario_externo'] = true;
            }

            if ($user) {
                // Usuario existente - actualizar si está configurado
                if ($config->auto_update_users) {
                    $user->update($userData);
                    Log::info('Usuario SAML2 actualizado', ['user_id' => $user->id]);
                }
            } else {
                // Usuario nuevo - crear si está configurado
                if ($config->auto_create_users) {
                    $user = User::create($userData);
                    Log::info('Usuario SAML2 creado', ['user_id' => $user->id]);
                } else {
                    Log::warning('Auto-creación de usuarios deshabilitada');
                    return null;
                }
            }

            // Asignar rol por defecto si no tiene ninguno
            if (!$user->hasAnyRole() && $config->default_role) {
                $user->assignRole($config->default_role);
                Log::info("Rol '{$config->default_role}' asignado al usuario SAML2", ['user_id' => $user->id]);
            }

            Log::info('Usuario SAML2 procesado exitosamente', [
                'user_id' => $user->id,
                'email' => $email,
                'okta_id' => $oktaId,
                'method' => 'saml2_okta'
            ]);

            return $user;

        } catch (\Exception $e) {
            Log::error('Error creando/actualizando usuario SAML2: ' . $e->getMessage());
            return null;
        }
    }

    protected function mapSamlFields(array $samlData, array $fieldMappings): array
    {
        $mappedData = [];

        foreach ($fieldMappings as $mapping) {
            $samlField = $mapping['saml_field'] ?? '';
            $userField = $mapping['user_field'] ?? '';
            $defaultValue = $mapping['default_value'] ?? '';
            $transformation = $mapping['transformation'] ?? '';

            if (!$samlField || !$userField) {
                continue;
            }

            $value = $samlData[$samlField] ?? $defaultValue;

            // Aplicar transformación si existe
            if ($transformation && $value) {
                try {
                    $value = eval("return $transformation;");
                } catch (\Exception $e) {
                    Log::warning('Error aplicando transformación', [
                        'field' => $samlField,
                        'transformation' => $transformation,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $mappedData[$userField] = $value;
        }

        return $mappedData;
    }

    public function getMetadata()
    {
        $config = Saml2OktaConfig::getActiveConfig();
        
        if (!$config) {
            abort(404, 'SAML2 configuration not found');
        }

        $metadata = '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="' . $config->sp_entity_id . '">
    <md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>' . $this->cleanCertificate($config->sp_x509_cert) . '</ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="' . $config->callback_url . '" index="0" isDefault="true"/>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="' . url('/saml2/logout') . '"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>';

        return response($metadata, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'inline; filename="metadata.xml"'
        ]);
    }

    protected function cleanCertificate($certificate)
    {
        // Limpiar el certificado removiendo BEGIN/END y saltos de línea
        return str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $certificate);
    }
}
