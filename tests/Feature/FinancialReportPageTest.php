<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\ProjectFinanceSplitItem;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FinancialReportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_financial_report_index_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $client = $this->createClient($workspace, 'PT Report Vision');
        $project = $this->createProject($workspace, $owner, $client, 'Report Project');
        $account = $this->createAccount($workspace, 'BCA Utama');

        ChartOfAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'code' => '4000',
            'name' => 'Revenue',
            'type' => 'revenue',
            'category' => 'service',
            'is_active' => true,
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'income',
            'category' => 'retainer',
            'amount' => 10000000,
            'description' => 'Income',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'expense',
            'category' => 'operational',
            'amount' => 2500000,
            'description' => 'Expense',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-202605-0010',
            'type' => 'invoice',
            'status' => 'paid',
            'subtotal' => 9000000,
            'discount_amount' => 0,
            'tax_rate' => 11,
            'tax_amount' => 990000,
            'total' => 9990000,
            'paid_amount' => 9990000,
            'currency' => 'IDR',
            'created_by' => $owner->getKey(),
        ]);

        $split = ProjectFinanceSplit::query()->create([
            'project_id' => $project->getKey(),
            'kas_kantor_percentage' => 10,
        ]);

        ProjectFinanceSplitItem::query()->create([
            'split_id' => $split->getKey(),
            'type' => 'team_fee',
            'component_type' => 'base_fee',
            'label' => 'Developer Fee',
            'user_id' => $owner->getKey(),
            'calculation_type' => 'flat',
            'flat_amount' => 1500000,
            'final_amount' => 1500000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Reimbursement::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'project_id' => $project->getKey(),
            'department' => 'marketing',
            'title' => 'Claim',
            'amount' => 300000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.reports.index', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Reports/Index')
                ->where('workspace.slug', 'velora')
                ->has('reports.summary')
                ->has('reports.profitLoss')
                ->has('reports.cashFlow')
                ->has('reports.balanceSheet')
                ->has('reports.projectCosts', 1)
                ->has('reports.taxReport')
                ->has('reports.divisionReports', 1)
                ->has('reports.employeeReports')
                ->has('chartOfAccounts.items', 1)
                ->has('filterOptions.periods', 3)
            );
    }

    public function test_chart_of_account_can_be_created_updated_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.reports.chart.store', $workspace), [
                'code' => '1000',
                'name' => 'Kas',
                'type' => 'asset',
                'category' => 'current_asset',
                'is_active' => true,
                'notes' => 'Cash account',
            ]);

        $create->assertRedirect();

        $coa = ChartOfAccount::query()->where('code', '1000')->firstOrFail();

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.reports.chart.update', [
                'workspace' => $workspace,
                'account' => $coa,
            ]), [
                'code' => '1000',
                'name' => 'Kas Utama',
                'type' => 'asset',
                'category' => 'current_asset',
                'is_active' => true,
                'notes' => 'Updated cash account',
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $coa->getKey(),
            'name' => 'Kas Utama',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.reports.chart.destroy', [
                'workspace' => $workspace,
                'account' => $coa,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('chart_of_accounts', [
            'id' => $coa->getKey(),
        ]);
    }

    protected function context(): array
    {
        return [
            User::query()->where('email', 'owner@kantordigital.test')->firstOrFail(),
            Workspace::query()->where('slug', 'velora')->firstOrFail(),
        ];
    }

    protected function createClient(Workspace $workspace, string $companyName): Client
    {
        return Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => $companyName,
            'pic_name' => 'Client PIC',
            'email' => strtolower(str_replace(' ', '', $companyName)) . '@client.test',
            'phone' => '081234567890',
            'status' => 'active',
            'portal_access' => false,
        ]);
    }

    protected function createProject(Workspace $workspace, User $owner, Client $client, string $name): Project
    {
        return Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => $name,
            'status' => 'active',
            'budget' => 12000000,
            'actual_cost' => 0,
            'progress' => 0,
            'created_by' => $owner->getKey(),
        ]);
    }

    protected function createAccount(Workspace $workspace, string $name): BankAccount
    {
        return BankAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'type' => 'bank',
            'balance' => 0,
            'is_active' => true,
        ]);
    }
}
