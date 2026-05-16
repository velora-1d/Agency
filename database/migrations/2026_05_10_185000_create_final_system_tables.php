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
        Schema::create('deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('website_id')->constrained('websites')->cascadeOnDelete();
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->string('environment', 20)->default('production');
            $table->string('platform', 20)->default('vps');
            $table->string('git_repo', 255)->nullable();
            $table->string('git_branch', 100)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('log')->nullable();
            $table->foreignUuid('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->jsonb('fields');
            $table->jsonb('settings')->nullable();
            $table->text('embed_code')->nullable();
            $table->boolean('auto_create_lead')->default(true);
            $table->integer('submission_count')->default(0);
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->jsonb('data');
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
        });

        Schema::create('social_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('title', 255)->nullable();
            $table->text('caption')->nullable();
            $table->text('hashtags')->nullable();
            $table->jsonb('platforms')->nullable(); // ['instagram', 'tiktok']
            $table->jsonb('media_files')->nullable();
            $table->string('status', 20)->default('idea');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->jsonb('analytics')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 100);
            $table->string('key_hash', 255)->unique();
            $table->jsonb('scopes')->nullable();
            $table->jsonb('ip_whitelist')->nullable();
            $table->integer('rate_limit_per_hour')->default(1000);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('type', 20); // 'internal', 'whatsapp'
            $table->string('name', 100)->nullable();
            $table->string('wa_contact_phone', 20)->nullable();
            $table->string('wa_contact_name', 100)->nullable();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('open');
            $table->string('label', 50)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content')->nullable();
            $table->string('type', 20)->default('text');
            $table->string('file_path', 255)->nullable();
            $table->string('wa_message_id', 100)->nullable();
            $table->boolean('is_from_client')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('help_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('category', 50)->nullable();
            $table->longText('content');
            $table->boolean('is_published')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('deployments');
    }
};
