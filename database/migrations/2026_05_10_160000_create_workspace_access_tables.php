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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('currency', 3)->default('IDR');
            $table->string('language', 5)->default('id');
            $table->string('custom_domain')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('wa_api_key')->nullable();
            $table->string('wa_phone_number', 20)->nullable();
            $table->string('n8n_webhook_url')->nullable();
            $table->time('working_hours_start')->default('08:00:00');
            $table->time('working_hours_end')->default('17:00:00');
            $table->unsignedInteger('storage_quota_gb')->default(50);
            $table->jsonb('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->uuid('parent_role_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('module', 50);
            $table->string('action', 20);
            $table->string('description')->nullable();

            $table->unique(['module', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();

            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('workspace_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->boolean('is_owner')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->unique(['workspace_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_users');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('workspaces');
    }
};
