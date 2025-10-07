<?php

namespace JohnRiveraGonzalez\Saml2Okta\Tests;

use JohnRiveraGonzalez\Saml2Okta\Saml2OktaPlugin;
use JohnRiveraGonzalez\Saml2Okta\Saml2OktaServiceProvider;
use Orchestra\Testbench\TestCase;

class Saml2OktaPluginTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            Saml2OktaServiceProvider::class,
        ];
    }

    public function test_plugin_can_be_instantiated()
    {
        $plugin = Saml2OktaPlugin::make();
        
        $this->assertInstanceOf(Saml2OktaPlugin::class, $plugin);
    }

    public function test_plugin_has_correct_id()
    {
        $plugin = Saml2OktaPlugin::make();
        
        $this->assertEquals('saml2-okta', $plugin->getId());
    }

    public function test_service_provider_is_registered()
    {
        $this->assertTrue($this->app->bound(Saml2OktaServiceProvider::class));
    }
}
