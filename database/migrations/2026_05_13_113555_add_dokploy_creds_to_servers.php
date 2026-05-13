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
        Schema::table('servers', function (Blueprint $table) {
            $table->string('dokploy_email', 100)->nullable()->after('control_panel_url');
            $table->string('dokploy_username', 100)->nullable()->after('dokploy_email');
            $table->text('dokploy_password')->nullable()->after('dokploy_username')->comment('Password Dokploy terenkripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['dokploy_email', 'dokploy_username', 'dokploy_password']);
        });
    }
};
