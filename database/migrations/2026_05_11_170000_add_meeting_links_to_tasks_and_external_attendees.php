<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table): void {
            $table->json('external_attendees')->nullable()->after('status');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->uuid('meeting_id')->nullable()->after('project_id');
            $table->foreign('meeting_id')->references('id')->on('meetings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['meeting_id']);
            $table->dropColumn('meeting_id');
        });

        Schema::table('meetings', function (Blueprint $table): void {
            $table->dropColumn('external_attendees');
        });
    }
};
