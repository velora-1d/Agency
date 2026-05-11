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
        Schema::table('activity_feed', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('activity_comments', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_feed', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });

        Schema::table('activity_comments', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
