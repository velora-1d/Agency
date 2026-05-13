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
        Schema::table('servers', function (Blueprint $blueprint) {
            $blueprint->integer('ssh_port')->default(22)->after('ip_address');
            $blueprint->string('ssh_user', 50)->default('root')->after('ssh_port');
            $blueprint->text('control_panel_url')->nullable()->after('os')->comment('Link Dokploy atau Panel Kontrol lainnya');
            $blueprint->text('ssh_password')->nullable()->after('ssh_user')->comment('Password SSH terenkripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['ssh_port', 'ssh_user', 'ssh_password', 'control_panel_url']);
        });
    }
};
