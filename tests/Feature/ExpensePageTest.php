<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\DepartmentBudget;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ExpensePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_expense_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $client = $this->createClient($workspace, 'PT Expense Jaya');
        $project = $this->createProject($workspace, $owner, $client, 'Expense Project');
        $cash = $this->createCashAccount($workspace, 'Kas Operasional');

        $reimbursement = Reimbursement::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'project_id' => $project->getKey(),
            'department' => 'marketing',
            'title' => 'Ads spend reimbursement',
            'amount' => 450000,
            'status' => 'paid',
            'paid_account_id' => $cash->getKey(),
            'approved_by' => $owner->getKey(),
            'approved_at' => now(),
            'paid_at' => now(),
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $cash->getKey(),
            'type' => 'expense',
            'category' => 'reimbursement',
            'amount' => 450000,
            'description' => 'Ads spend reimbursement',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        DepartmentBudget::query()->create([
            'workspace_id' => $workspace->getKey(),
            'department' => 'marketing',
            'period_label' => 'monthly',
            'limit_amount' => 300000,
            'notes' => 'Monthly ads budget',
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.expenses.index', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Expenses/Index')
                ->where('workspace.slug', 'velora')
                ->where('reimbursements.summary.total_reimbursements', 1)
                ->where('reimbursements.summary.paid_reimbursements', 1)
                ->where('budgets.summary.total_budgets', 1)
                ->where('budgets.items.0.over_budget', true)
                ->has('filterOptions.users')
                ->has('filterOptions.projects', 1)
                ->has('filterOptions.accounts', 1)
            );
    }

    public function test_reimbursement_and_budget_can_be_managed(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $client = $this->createClient($workspace, 'PT Expense Flow');
        $project = $this->createProject($workspace, $owner, $client, 'Expense Flow Project');
        $cash = $this->createCashAccount($workspace, 'Kas Marketing');

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.expenses.reimbursements.store', $workspace), [
                'user_id' => $owner->getKey(),
                'project_id' => $project->getKey(),
                'department' => 'marketing',
                'title' => 'Conference ticket',
                'amount' => 250000,
                'status' => 'pending',
                'notes' => 'Need approval',
            ]);

        $create->assertRedirect();

        $reimbursement = Reimbursement::query()->where('title', 'Conference ticket')->firstOrFail();

        $approve = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.expenses.reimbursements.status.update', [
                'workspace' => $workspace,
                'reimbursement' => $reimbursement,
            ]), [
                'status' => 'approved',
            ]);

        $approve->assertRedirect();

        $paid = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.expenses.reimbursements.status.update', [
                'workspace' => $workspace,
                'reimbursement' => $reimbursement,
            ]), [
                'status' => 'paid',
                'paid_account_id' => $cash->getKey(),
            ]);

        $paid->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'account_id' => $cash->getKey(),
            'category' => 'reimbursement',
            'amount' => 250000,
        ]);

        $budgetCreate = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.expenses.budgets.store', $workspace), [
                'department' => 'marketing',
                'period_label' => 'monthly',
                'limit_amount' => 1000000,
                'notes' => 'Marketing budget',
            ]);

        $budgetCreate->assertRedirect();

        $budget = DepartmentBudget::query()->where('department', 'marketing')->firstOrFail();

        $budgetUpdate = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.expenses.budgets.update', [
                'workspace' => $workspace,
                'budget' => $budget,
            ]), [
                'department' => 'marketing',
                'period_label' => 'monthly',
                'limit_amount' => 1200000,
                'notes' => 'Updated budget',
            ]);

        $budgetUpdate->assertRedirect();

        $budgetDelete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.expenses.budgets.destroy', [
                'workspace' => $workspace,
                'budget' => $budget,
            ]));

        $budgetDelete->assertRedirect();

        $reimbursementDelete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.expenses.reimbursements.destroy', [
                'workspace' => $workspace,
                'reimbursement' => $reimbursement,
            ]));

        $reimbursementDelete->assertRedirect();
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
            'budget' => 5000000,
            'actual_cost' => 0,
            'progress' => 0,
            'created_by' => $owner->getKey(),
        ]);
    }

    protected function createCashAccount(Workspace $workspace, string $name): BankAccount
    {
        return BankAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'type' => 'cash',
            'balance' => 0,
            'is_active' => true,
        ]);
    }
}
