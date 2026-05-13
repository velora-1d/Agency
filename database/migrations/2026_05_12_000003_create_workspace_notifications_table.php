<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->string('category', 50)->default('general');
            $table->string('tone', 20)->default('neutral');
            $table->string('status', 20)->default('unread');
            $table->string('source_type', 100)->nullable();
            $table->uuid('source_id')->nullable();
            $table->string('action_url', 255)->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_notifications');
    }
};
