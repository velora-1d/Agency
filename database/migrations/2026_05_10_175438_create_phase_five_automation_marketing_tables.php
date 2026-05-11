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
        Schema::create('automation_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('trigger_event', 50); // 'lead_created', 'invoice_paid', etc
            $table->string('n8n_workflow_id', 50)->nullable();
            $table->string('n8n_webhook_url', 500)->nullable();
            $table->jsonb('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 20); // 'google_ads', 'meta_ads', 'email', 'wa'
            $table->string('status', 20)->default('planning');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('spend', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->jsonb('external_ids')->nullable(); // Store Ads IDs
            $table->timestamps();
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('subject', 255);
            $table->longText('content');
            $table->string('status', 20)->default('draft'); // 'draft', 'scheduled', 'sending', 'sent'
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('email', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'email']);
        });

        Schema::create('wa_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('instance_name', 100);
            $table->string('status', 20)->default('disconnected'); // 'connected', 'disconnected', 'pairing'
            $table->string('phone_number', 20)->nullable();
            $table->string('apikey', 255)->nullable();
            $table->jsonb('config')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'instance_name']);
        });

        Schema::create('wa_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('session_id')->constrained('wa_sessions')->cascadeOnDelete();
            $table->string('direction', 10); // 'inbound', 'outbound'
            $table->string('remote_jid', 50); // phone number@s.whatsapp.net
            $table->text('body')->nullable();
            $table->string('type', 20)->default('text'); // 'text', 'image', 'file', 'button'
            $table->string('status', 20)->default('pending'); // 'sent', 'delivered', 'read', 'failed'
            $table->string('external_msg_id', 100)->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->jsonb('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_sessions');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('automation_workflows');
    }
};
