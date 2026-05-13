<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PayrollPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_payroll_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $client = $this->createClient($workspace, 'PT Komisi Nusantara');
        $project = $this->createProject($workspace, $owner, $client, 'Closing Project', 5000000);

        $split = ProjectFinanceSplit::query()->create([
            'project_id' => $project->getKey(),
            'template_name' => 'Agency Standard',
            'kas_kantor_percentage' => 10,
            'payment_trigger' => 'dp',
        ]);

        $split->items()->createMany([
            [
                'type' => 'operational',
                'component_type' => 'operational',
                'label' => 'Server Cost',
                'flat_amount' => 500000,
                'status' => 'pending',
            ],
            [
                'type' => 'team_fee',
                'component_type' => 'base_fee',
                'label' => 'Developer Fee',
                'user_id' => $owner->getKey(),
                'calculation_type' => 'percentage',
                'percentage' => 30,
                'status' => 'pending',
            ],
            [
                'type' => 'team_fee',
                'component_type' => 'commission',
                'label' => 'Sales Commission',
                'user_id' => $owner->getKey(),
                'calculation_type' => 'flat',
                'flat_amount' => 250000,
                'status' => 'paid',
                'paid_at' => now(),
            ],
        ]);

        app(\App\Services\Finance\FinanceSplitService::class)->calculate($split->fresh()->load('project.invoices', 'items'));

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.payroll.index', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Payroll/Index')
                ->where('workspace.slug', 'velora')
                ->where('splits.summary.total_splits', 1)
                ->where('splits.summary.team_payout_items', 2)
                ->where('splits.summary.paid_payout_items', 1)
                ->where('splits.items.0.project.name', 'Closing Project')
                ->where('splits.items.0.items.1.component_type', 'base_fee')
                ->where('members.0.user_name', $owner->name)
                ->has('filterOptions.projects', 1)
                ->has('filterOptions.users')
                ->has('filterOptions.componentTypes', 5)
            );
    }

    public function test_payroll_split_can_be_created_updated_marked_paid_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $client = $this->createClient($workspace, 'PT Payroll Maju');
        $project = $this->createProject($workspace, $owner, $client, 'Payroll Project', 8000000);

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.payroll.store', $workspace), [
                'project_id' => $project->getKey(),
                'template_name' => 'Launch Template',
                'kas_kantor_percentage' => 12.5,
                'payment_trigger' => 'full_paid',
                'items' => [
                    [
                        'type' => 'operational',
                        'component_type' => 'operational',
                        'label' => 'Ads Cost',
                        'flat_amount' => 700000,
                    ],
                    [
                        'type' => 'team_fee',
                        'component_type' => 'bonus',
                        'label' => 'Project Bonus',
                        'user_id' => $owner->getKey(),
                        'calculation_type' => 'flat',
                        'flat_amount' => 500000,
                    ],
                ],
            ]);

        $create->assertRedirect();

        $split = ProjectFinanceSplit::query()->where('template_name', 'Launch Template')->firstOrFail();

        $this->assertDatabaseHas('project_finance_splits', [
            'id' => $split->getKey(),
            'project_id' => $project->getKey(),
            'payment_trigger' => 'full_paid',
        ]);

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.payroll.update', [
                'workspace' => $workspace,
                'split' => $split,
            ]), [
                'project_id' => $project->getKey(),
                'template_name' => 'Launch Template Revised',
                'kas_kantor_percentage' => 15,
                'payment_trigger' => 'custom',
                'payment_trigger_custom' => 'Saat milestone kedua',
                'items' => [
                    [
                        'type' => 'operational',
                        'component_type' => 'operational',
                        'label' => 'Production Cost',
                        'flat_amount' => 900000,
                    ],
                    [
                        'type' => 'team_fee',
                        'component_type' => 'commission',
                        'label' => 'Closing Commission',
                        'user_id' => $owner->getKey(),
                        'calculation_type' => 'flat',
                        'flat_amount' => 650000,
                    ],
                ],
            ]);

        $update->assertRedirect();

        $split = $split->fresh();
        $item = $split->items()->where('type', 'team_fee')->firstOrFail();

        $this->assertDatabaseHas('project_finance_splits', [
            'id' => $split->getKey(),
            'template_name' => 'Launch Template Revised',
            'payment_trigger' => 'custom',
        ]);

        $markPaid = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.payroll.items.status.update', [
                'workspace' => $workspace,
                'split' => $split,
                'item' => $item,
            ]), [
                'status' => 'paid',
            ]);

        $markPaid->assertRedirect();

        $this->assertDatabaseHas('project_finance_split_items', [
            'id' => $item->getKey(),
            'status' => 'paid',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.payroll.destroy', [
                'workspace' => $workspace,
                'split' => $split,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('project_finance_splits', [
            'id' => $split->getKey(),
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

    protected function createProject(Workspace $workspace, User $owner, Client $client, string $name, float $budget): Project
    {
        return Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => $name,
            'status' => 'active',
            'budget' => $budget,
            'actual_cost' => 0,
            'progress' => 0,
            'created_by' => $owner->getKey(),
        ]);
    }
}
