<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->timestamp('last_reconciled_at')->nullable()->after('is_active');
            $table->text('reconciliation_notes')->nullable()->after('last_reconciled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->dropColumn(['last_reconciled_at', 'reconciliation_notes']);
        });
    }
};
