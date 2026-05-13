<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class BillingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_billing_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->billingContext();
        $client = $this->createClient($workspace, 'PT Retainer Hebat');
        $project = $this->createProject($workspace, $owner, $client, 'Social Media Retainer');

        $billing = Billing::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'name' => 'Monthly Social Media Retainer',
            'type' => 'retainer',
            'amount' => 2500000,
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'next_invoice_date' => now()->toDateString(),
            'status' => 'active',
            'notes' => 'Retainer for content production.',
        ]);

        $invoice = Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'billing_id' => $billing->getKey(),
            'number' => 'INV-202605-0009',
            'type' => 'invoice',
            'status' => 'partial',
            'subtotal' => 2500000,
            'discount_amount' => 0,
            'tax_rate' => 11,
            'tax_amount' => 275000,
            'total' => 2775000,
            'paid_amount' => 1000000,
            'currency' => 'IDR',
            'due_date' => now()->addDays(7)->toDateString(),
            'is_recurring' => true,
            'recurrence_rule' => 'monthly',
            'created_by' => $owner->getKey(),
        ]);

        Payment::query()->create([
            'workspace_id' => $workspace->getKey(),
            'invoice_id' => $invoice->getKey(),
            'amount' => 1000000,
            'method' => 'manual_transfer',
            'status' => 'verified',
            'verified_by' => $owner->getKey(),
            'verified_at' => now(),
            'paid_at' => now(),
        ]);

        Billing::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => 'Website Project Billing',
            'type' => 'project_based',
            'amount' => 6000000,
            'billing_cycle' => 'quarterly',
            'start_date' => now()->toDateString(),
            'next_invoice_date' => now()->addDays(10)->toDateString(),
            'status' => 'paused',
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.billings.index', [
                'workspace' => $workspace,
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Billings/Index')
                ->where('workspace.slug', 'velora')
                ->where('billings.summary.total_billings', 2)
                ->where('billings.summary.active_billings', 1)
                ->where('billings.summary.paused_billings', 1)
                ->where('billings.summary.invoices_generated', 1)
                ->where('billings.items.0.name', 'Monthly Social Media Retainer')
                ->where('billings.items.0.client.name', 'PT Retainer Hebat')
                ->where('billings.items.0.payment_history.0.invoice_number', 'INV-202605-0009')
                ->where('billings.items.0.recent_invoices.0.number', 'INV-202605-0009')
                ->has('filterOptions.clients', 1)
                ->has('filterOptions.projects', 1)
                ->has('filterOptions.types', 2)
                ->has('filterOptions.statuses', 3)
                ->has('filterOptions.billingCycles', 3)
            );
    }

    public function test_billing_can_be_created_updated_generated_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->billingContext();
        $client = $this->createClient($workspace, 'PT Billing Tumbuh');
        $project = $this->createProject($workspace, $owner, $client, 'Growth Retainer');

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.billings.store', $workspace), [
                'client_id' => $client->getKey(),
                'project_id' => $project->getKey(),
                'name' => 'Growth Retainer',
                'type' => 'retainer',
                'amount' => 3500000,
                'billing_cycle' => 'monthly',
                'start_date' => now()->startOfMonth()->toDateString(),
                'next_invoice_date' => now()->toDateString(),
                'status' => 'active',
                'notes' => 'Monthly strategy and reporting.',
            ]);

        $create->assertRedirect();

        $billing = Billing::query()->where('name', 'Growth Retainer')->firstOrFail();

        $this->assertDatabaseHas('billings', [
            'id' => $billing->getKey(),
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'status' => 'active',
        ]);

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.billings.update', [
                'workspace' => $workspace,
                'billing' => $billing,
            ]), [
                'client_id' => $client->getKey(),
                'project_id' => $project->getKey(),
                'name' => 'Growth Retainer Updated',
                'type' => 'project_based',
                'amount' => 4200000,
                'billing_cycle' => 'quarterly',
                'start_date' => now()->startOfMonth()->toDateString(),
                'next_invoice_date' => now()->toDateString(),
                'status' => 'active',
                'notes' => 'Updated billing setup.',
            ]);

        $update->assertRedirect();

        $billing = $billing->fresh();

        $this->assertSame('Growth Retainer Updated', $billing->name);
        $this->assertSame('project_based', $billing->type);
        $this->assertSame('4200000.00', $billing->amount);

        $generate = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.billings.generate-invoice', [
                'workspace' => $workspace,
                'billing' => $billing,
            ]));

        $generate->assertRedirect();

        $invoice = Invoice::query()->where('billing_id', $billing->getKey())->firstOrFail();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->getKey(),
            'workspace_id' => $workspace->getKey(),
            'billing_id' => $billing->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'status' => 'draft',
        ]);

        $this->assertSame(1, $invoice->items()->count());
        $this->assertTrue($billing->fresh()->next_invoice_date->gt(now()->startOfDay()));

        Billing::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => 'Second Due Billing',
            'type' => 'retainer',
            'amount' => 1500000,
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'next_invoice_date' => now()->subDay()->toDateString(),
            'status' => 'active',
        ]);

        $generateDue = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.billings.generate-due', $workspace));

        $generateDue->assertRedirect();

        $this->assertSame(2, Invoice::query()->where('workspace_id', $workspace->getKey())->count());

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.billings.destroy', [
                'workspace' => $workspace,
                'billing' => $billing,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('billings', [
            'id' => $billing->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function billingContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
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
            'budget' => 10000000,
            'actual_cost' => 0,
            'progress' => 0,
            'created_by' => $owner->getKey(),
        ]);
    }
}
