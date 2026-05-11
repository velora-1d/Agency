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
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('signed_at')->nullable()->after('signed_file_path');
            $table->string('signed_by_name')->nullable()->after('signed_at');
            $table->string('signed_ip', 45)->nullable()->after('signed_by_name');
            $table->string('esign_token', 64)->nullable()->unique()->after('signed_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['signed_at', 'signed_by_name', 'signed_ip', 'esign_token']);
        });
    }
};
