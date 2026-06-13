<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('okta_id')->nullable()->after('email_verified_at');
            $table->string('auth_method')->nullable()->after('okta_id');
            
            // Índice para búsquedas rápidas por okta_id
            $table->index('okta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['okta_id']);
            $table->dropColumn(['okta_id', 'auth_method']);
        });
    }
};

