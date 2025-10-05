<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saml2_okta_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_id');
            $table->text('client_secret');
            $table->string('callback_url');
            $table->string('idp_entity_id');
            $table->string('idp_sso_url');
            $table->string('idp_slo_url')->nullable();
            $table->string('idp_metadata_url')->nullable();
            $table->text('idp_x509_cert');
            $table->string('sp_entity_id');
            $table->text('sp_x509_cert');
            $table->text('sp_private_key');
            $table->boolean('is_active')->default(false);
            $table->string('button_label')->default('Iniciar sesión con Okta');
            $table->string('button_icon')->default('heroicon-o-shield-check');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saml2_okta_configs');
    }
};
