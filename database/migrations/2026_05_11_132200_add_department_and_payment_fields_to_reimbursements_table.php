<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursements', function (Blueprint $table): void {
            $table->string('department', 50)->nullable()->after('title');
            $table->foreignUuid('paid_account_id')->nullable()->after('approved_by')->constrained('bank_accounts')->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('paid_account_id');
            $table->dropColumn(['department', 'paid_at']);
        });
    }
};
