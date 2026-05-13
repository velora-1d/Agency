<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FinanceOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_finance_overview_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->financeContext();
        $client = $this->createClient($workspace, 'PT Finansial Mantap');
        $project = $this->createProject($workspace, $owner, $client, 'Retainer Growth');
        $account = $this->createAccount($workspace, 'BCA Operasional', 0);

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-001',
            'status' => 'paid',
            'subtotal' => 250000,
            'tax_rate' => 11,
            'tax_amount' => 27500,
            'total' => 277500,
            'paid_amount' => 277500,
            'currency' => 'IDR',
            'paid_at' => now()->subDay(),
            'created_by' => $owner->getKey(),
        ]);

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-002',
            'status' => 'partial',
            'subtotal' => 180000,
            'tax_rate' => 11,
            'tax_amount' => 19800,
            'total' => 199800,
            'paid_amount' => 30000,
            'currency' => 'IDR',
            'due_date' => now()->addDays(7)->toDateString(),
            'created_by' => $owner->getKey(),
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'income',
            'category' => 'manual-retainer',
            'amount' => 100000,
            'description' => 'Manual income',
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
            'amount' => 40000,
            'description' => 'Tool subscription',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        ProjectFinanceSplit::query()->create([
            'project_id' => $project->getKey(),
            'template_name' => 'Standard Split',
            'kas_kantor_percentage' => 10,
            'kas_kantor_amount' => 30000,
            'payment_trigger' => 'dp',
            'total_project_value' => 300000,
            'total_operational_cost' => 50000,
            'total_kas_kantor' => 30000,
            'total_team_fee' => 220000,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.overview', [
                'workspace' => $workspace,
                'period' => 'month',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Overview')
                ->where('workspace.slug', 'velora')
                ->where('filters.period', 'month')
                ->where('finance.summary.outstanding_count', 1)
                ->where('finance.summary.income', 377500)
                ->where('finance.summary.expense', 40000)
                ->where('finance.summary.profit', 337500)
                ->has('finance.cashFlow.labels', 6)
                ->has('finance.outstandingInvoices', 1)
                ->has('finance.revenueByClient', 1)
                ->has('finance.revenueByProject', 1)
                ->has('finance.transactions', 2)
                ->has('filterOptions.accounts', 1)
                ->has('filterOptions.transactionTypes', 2)
            );
    }

    public function test_transaction_can_be_created_updated_and_deleted_from_finance_overview(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->financeContext();
        $client = $this->createClient($workspace, 'PT Ledger Nusantara');
        $project = $this->createProject($workspace, $owner, $client, 'Ledger Project');
        $account = $this->createAccount($workspace, 'Mandiri Operasional', 0);

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.transactions.store', $workspace), [
                'account_id' => $account->getKey(),
                'project_id' => $project->getKey(),
                'type' => 'income',
                'category' => 'retainer',
                'amount' => 125000,
                'description' => 'Project retainer',
                'date' => now()->toDateString(),
            ]);

        $create->assertRedirect();

        $transaction = Transaction::query()->where('category', 'retainer')->firstOrFail();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->getKey(),
            'workspace_id' => $workspace->getKey(),
            'type' => 'income',
            'amount' => 125000,
        ]);

        $this->assertSame('125000.00', $account->fresh()->balance);

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.transactions.update', [
                'workspace' => $workspace,
                'transaction' => $transaction,
            ]), [
                'account_id' => $account->getKey(),
                'project_id' => $project->getKey(),
                'type' => 'expense',
                'category' => 'tools',
                'amount' => 45000,
                'description' => 'Updated transaction',
                'date' => now()->toDateString(),
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->getKey(),
            'type' => 'expense',
            'amount' => 45000,
            'category' => 'tools',
        ]);

        $this->assertSame('-45000.00', $account->fresh()->balance);

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
    protected function financeContext(): array
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
}
