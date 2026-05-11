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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('bank_name', 50)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 100)->nullable();
            $table->string('type', 20)->default('bank'); // 'bank', 'cash', 'e-wallet'
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('contact_name', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('billing_cycle', 10)->nullable(); // 'monthly', 'yearly'
            $table->string('status', 20)->default('active');
            $table->date('next_renewal_date')->nullable();
            $table->integer('reminder_days_before')->default(7);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('number', 50)->unique();
            $table->string('title', 255);
            $table->text('cover_letter')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('timeline')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('status', 20)->default('draft'); // 'draft', 'sent', 'approved', 'rejected', 'revised'
            $table->integer('version')->default(1);
            $table->uuid('parent_quotation_id')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('dp_percentage', 5, 2)->nullable();
            $table->decimal('dp_amount', 15, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_token', 255)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('parent_quotation_id')->references('id')->on('quotations')->nullOnDelete();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable(); // 'development', 'server', 'domain', etc
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->integer('order_index')->default(0);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUuid('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('number', 50)->unique();
            $table->string('type', 20)->default('invoice'); // 'proforma', 'invoice', 'credit_note'
            $table->string('status', 20)->default('draft'); // 'draft', 'sent', 'partial', 'paid', 'overdue'
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->date('due_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 100)->nullable();
            $table->string('payment_method', 20)->nullable(); // 'pakasir', 'manual'
            $table->string('pakasir_order_id', 100)->nullable();
            $table->string('pakasir_payment_url', 500)->nullable();
            $table->timestamp('internal_approved_at')->nullable();
            $table->foreignUuid('internal_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->integer('order_index')->default(0);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('method', 20); // 'pakasir_qris', 'pakasir_va', 'manual_tf'
            $table->string('status', 20)->default('pending'); // 'pending', 'verified', 'rejected'
            $table->string('pakasir_transaction_id', 100)->nullable();
            $table->string('proof_file_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('type', 10); // 'income', 'expense'
            $table->string('category', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->date('date');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('project_finance_splits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('template_name', 100)->nullable();
            $table->decimal('kas_kantor_percentage', 5, 2)->nullable();
            $table->decimal('kas_kantor_amount', 15, 2)->nullable();
            $table->string('payment_trigger', 20)->nullable(); // 'dp', 'completion', 'full_paid', 'custom'
            $table->text('payment_trigger_custom')->nullable();
            $table->decimal('total_project_value', 15, 2)->nullable();
            $table->decimal('total_operational_cost', 15, 2)->nullable();
            $table->decimal('total_kas_kantor', 15, 2)->nullable();
            $table->decimal('total_team_fee', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('project_finance_split_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('split_id')->constrained('project_finance_splits')->cascadeOnDelete();
            $table->string('type', 20); // 'operational', 'team_fee'
            $table->string('label', 100)->nullable(); // 'Biaya Server', 'Developer Fee', etc
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('calculation_type', 10)->nullable(); // 'percentage', 'flat'
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('flat_amount', 15, 2)->nullable();
            $table->decimal('final_amount', 15, 2)->nullable();
            $table->string('status', 20)->default('pending'); // 'pending', 'paid'
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_finance_split_items');
        Schema::dropIfExists('project_finance_splits');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('bank_accounts');
    }
};
