<?php

namespace JohnRiveraGonzalez\Saml2Okta\Controllers;

use Illuminate\Http\Request;
use JohnRiveraGonzalez\Saml2Okta\Services\Saml2Service;

class Saml2Controller
{
    protected Saml2Service $saml2Service;

    public function __construct(Saml2Service $saml2Service)
    {
        $this->saml2Service = $saml2Service;
    }

    /**
     * Redirigir a SAML2 IdP
     */
    public function redirect()
    {
        return $this->saml2Service->redirect();
    }

    /**
     * Manejar callback SAML2
     */
    public function callback(Request $request)
    {
        return $this->saml2Service->handleCallback($request);
    }

    public function metadata()
    {
        return $this->saml2Service->getMetadata();
    }

    public function config()
    {
        $config = \JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig::getActiveConfig();
        
        if (!$config) {
            return response()->json(['is_active' => false]);
        }

        return response()->json([
            'is_active' => $config->is_active,
            'button_label' => $config->button_label,
            'button_icon' => $config->button_icon,
            'login_url' => route('saml2.login'),
        ]);
    }
}
