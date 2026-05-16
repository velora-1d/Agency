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
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('ip_address', 50)->nullable();
            $table->string('provider', 50)->nullable(); // 'digitalocean', 'linode', 'vps-local'
            $table->string('type', 20)->default('vps'); // 'vps', 'shared', 'dedicated'
            $table->string('location', 50)->nullable();
            $table->jsonb('specs')->nullable(); // CPU, RAM, Disk
            $table->string('os', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('domain_name', 255)->unique();
            $table->string('registrar', 100)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('auto_renew')->default(false);
            $table->jsonb('dns_records')->nullable();
            $table->timestamp('last_whois_at')->nullable();
            $table->timestamps();
        });

        Schema::create('websites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->foreignUuid('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('name', 100);
            $table->string('url', 255);
            $table->string('cms', 50)->nullable(); // 'wordpress', 'laravel', 'custom'
            $table->string('php_version', 10)->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('ssl_enabled')->default(true);
            $table->date('ssl_expiry_date')->nullable();
            $table->timestamp('last_uptime_check_at')->nullable();
            $table->integer('uptime_percentage')->nullable();
            $table->timestamps();
        });

        Schema::create('service_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('service_name', 100); // 'CPanel', 'SSH', 'WP-Admin'
            $table->morphs('credentialable'); // Relates to Website, Server, or Domain
            $table->string('username', 100)->nullable();
            $table->text('password')->nullable(); // Encrypted
            $table->string('url', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('workspaces', function (Blueprint $table) {
            if (!Schema::hasColumn('workspaces', 'custom_domain')) {
                $table->string('custom_domain')->nullable();
            }
            if (!Schema::hasColumn('workspaces', 'branding_config')) {
                $table->jsonb('branding_config')->nullable(); // logo, colors, etc
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['custom_domain', 'branding_config']);
        });
        Schema::dropIfExists('service_credentials');
        Schema::dropIfExists('websites');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('servers');
    }
};
