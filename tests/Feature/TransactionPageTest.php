<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TransactionPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_transaction_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->transactionContext();
        $client = $this->createClient($workspace, 'PT Ledger Mantap');
        $project = $this->createProject($workspace, $owner, $client, 'Growth Retainer');
        $account = $this->createAccount($workspace, 'BCA Operasional', 0);
        $invoice = $this->createInvoice($workspace, $owner, $client, $project);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'invoice_id' => $invoice->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'income',
            'category' => 'invoice_payment',
            'amount' => 250000,
            'description' => 'Payment from invoice',
            'attachment_path' => 'https://example.test/proof.pdf',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'expense',
            'category' => 'tools',
            'amount' => 75000,
            'description' => 'Tool subscription',
            'date' => now()->subDay()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.transactions.index', [
                'workspace' => $workspace,
                'type' => 'income',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Transactions/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.type', 'income')
                ->where('transactions.summary.total_transactions', 1)
                ->where('transactions.summary.invoice_linked_count', 1)
                ->where('transactions.summary.attachment_count', 1)
                ->where('transactions.items.0.category', 'invoice_payment')
                ->where('transactions.items.0.entry_mode', 'invoice_linked')
                ->has('filterOptions.categories')
                ->has('filterOptions.clients', 1)
                ->has('filterOptions.projects', 1)
                ->has('filterOptions.invoices', 1)
                ->has('filterOptions.accounts', 1)
            );
    }

    public function test_transaction_page_supports_crud_and_csv_export(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->transactionContext();
        $client = $this->createClient($workspace, 'PT Rekap Nusantara');
        $project = $this->createProject($workspace, $owner, $client, 'Ops Project');
        $account = $this->createAccount($workspace, 'Mandiri Operasional', 0);
        $invoice = $this->createInvoice($workspace, $owner, $client, $project);

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.transactions.store', $workspace), [
                'account_id' => $account->getKey(),
                'invoice_id' => $invoice->getKey(),
                'project_id' => $project->getKey(),
                'type' => 'expense',
                'category' => 'operasional',
                'amount' => 125000,
                'description' => 'Office operational cost',
                'attachment_path' => 'receipts/ops-may.pdf',
                'date' => now()->toDateString(),
            ]);

        $create->assertRedirect();

        $transaction = Transaction::query()->where('category', 'operasional')->firstOrFail();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->getKey(),
            'workspace_id' => $workspace->getKey(),
            'invoice_id' => $invoice->getKey(),
            'attachment_path' => 'receipts/ops-may.pdf',
        ]);

        $this->assertSame('-125000.00', $account->fresh()->balance);

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.transactions.update', [
                'workspace' => $workspace,
                'transaction' => $transaction,
            ]), [
                'account_id' => $account->getKey(),
                'invoice_id' => null,
                'project_id' => $project->getKey(),
                'type' => 'income',
                'category' => 'manual_income',
                'amount' => 300000,
                'description' => 'Manual top up',
                'attachment_path' => 'receipts/topup.pdf',
                'date' => now()->toDateString(),
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->getKey(),
            'type' => 'income',
            'category' => 'manual_income',
            'amount' => 300000,
            'attachment_path' => 'receipts/topup.pdf',
        ]);

        $this->assertSame('300000.00', $account->fresh()->balance);

        $export = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.transactions.export', [
                'workspace' => $workspace,
                'category' => 'manual_income',
            ]));

        $export->assertOk();
        $export->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('manual_income', $export->streamedContent());
        $this->assertStringContainsString('Manual entry', $export->streamedContent());

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.transactions.destroy', [
                'workspace' => $workspace,
                'transaction' => $transaction,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->getKey(),
        ]);
        $this->assertSame('0.00', $account->fresh()->balance);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function transactionContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
    }

    protected function createClient(Workspace $workspace, string $name): Client
    {
        return Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => $name,
            'status' => 'active',
            'portal_access' => true,
        ]);
    }

    protected function createProject(Workspace $workspace, User $owner, Client $client, string $name): Project
    {
        return Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => $name,
            'status' => 'active',
            'created_by' => $owner->getKey(),
        ]);
    }

    protected function createAccount(Workspace $workspace, string $name, float $balance): BankAccount
    {
        return BankAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'type' => 'bank',
            'balance' => $balance,
            'is_active' => true,
        ]);
    }

    protected function createInvoice(Workspace $workspace, User $owner, Client $client, Project $project): Invoice
    {
        return Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-TXN-001',
            'status' => 'paid',
            'subtotal' => 250000,
            'tax_rate' => 11,
            'tax_amount' => 27500,
            'total' => 277500,
            'paid_amount' => 277500,
            'currency' => 'IDR',
            'paid_at' => now(),
            'created_by' => $owner->getKey(),
        ]);
    }
}
