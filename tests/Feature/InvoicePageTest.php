<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InvoicePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_invoice_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->invoiceContext();
        $client = $this->createClient($workspace, 'PT Invoice Mantap');
        $project = $this->createProject($workspace, $owner, $client, 'Retainer Project');

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-202605-0001',
            'type' => 'proforma',
            'status' => 'draft',
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_rate' => 11,
            'tax_amount' => 27500,
            'total' => 277500,
            'paid_amount' => 0,
            'currency' => 'IDR',
            'due_date' => now()->addDays(10)->toDateString(),
            'is_recurring' => true,
            'recurrence_rule' => 'monthly',
            'payment_method' => 'pakasir_qris',
            'created_by' => $owner->getKey(),
        ])->items()->create([
            'name' => 'Retainer',
            'description' => 'Monthly retainer',
            'quantity' => 1,
            'unit_price' => 250000,
            'subtotal' => 250000,
            'order_index' => 1,
        ]);

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-202605-0002',
            'type' => 'invoice',
            'status' => 'sent',
            'subtotal' => 180000,
            'discount_amount' => 0,
            'tax_rate' => 11,
            'tax_amount' => 19800,
            'total' => 199800,
            'paid_amount' => 50000,
            'currency' => 'IDR',
            'due_date' => now()->subDays(2)->toDateString(),
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.invoices.index', [
                'workspace' => $workspace,
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Invoices/Index')
                ->where('workspace.slug', 'velora')
                ->where('invoices.summary.total_invoices', 2)
                ->where('invoices.summary.proforma_invoices', 1)
                ->where('invoices.summary.recurring_invoices', 1)
                ->where('invoices.summary.overdue_invoices', 1)
                ->where('invoices.items.0.effective_status', 'overdue')
                ->where('invoices.items.1.type_label', 'Proforma')
                ->has('filterOptions.clients')
                ->has('filterOptions.projects')
                ->has('filterOptions.contracts')
                ->has('filterOptions.types', 3)
                ->has('filterOptions.paymentMethods', 3)
                ->has('filterOptions.recurrenceRules', 4)
            );
    }

    public function test_invoice_can_be_created_updated_approved_sent_paid_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->invoiceContext();
        $client = $this->createClient($workspace, 'PT Collection Studio');
        $project = $this->createProject($workspace, $owner, $client, 'Collection Project');
        $contract = $this->createContract($workspace, $owner, $client, $project);
        $quotation = $this->createApprovedQuotation($workspace, $owner, $client);

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.invoices.store', $workspace), [
                'quotation_id' => $quotation->getKey(),
                'contract_id' => $contract->getKey(),
                'type' => 'proforma',
                'currency' => 'IDR',
                'due_date' => now()->addDays(14)->toDateString(),
                'discount_amount' => 10000,
                'tax_rate' => 11,
                'is_recurring' => true,
                'recurrence_rule' => 'monthly',
                'payment_method' => 'pakasir_qris',
                'pakasir_order_id' => 'PKS-ORDER-001',
                'pakasir_payment_url' => 'https://pay.example.test/invoice/1',
                'notes' => 'DP invoice for retainer.',
            ]);

        $create->assertRedirect();

        $invoice = Invoice::query()->where('quotation_id', $quotation->getKey())->firstOrFail();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->getKey(),
            'workspace_id' => $workspace->getKey(),
            'quotation_id' => $quotation->getKey(),
            'contract_id' => $contract->getKey(),
            'type' => 'proforma',
            'status' => 'draft',
        ]);

        $this->assertSame(2, $invoice->items()->count());

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.invoices.update', [
                'workspace' => $workspace,
                'invoice' => $invoice,
            ]), [
                'quotation_id' => $quotation->getKey(),
                'contract_id' => $contract->getKey(),
                'type' => 'proforma',
                'currency' => 'IDR',
                'due_date' => now()->addDays(21)->toDateString(),
                'discount_amount' => 15000,
                'tax_rate' => 11,
                'is_recurring' => true,
                'recurrence_rule' => 'monthly',
                'payment_method' => 'pakasir_qris',
                'pakasir_order_id' => 'PKS-ORDER-002',
                'pakasir_payment_url' => 'https://pay.example.test/invoice/2',
                'notes' => 'Updated invoice notes.',
            ]);

        $update->assertRedirect();

        $invoice = $invoice->fresh();

        $this->assertSame('Updated invoice notes.', $invoice->notes);
        $this->assertSame('15000.00', $invoice->discount_amount);

        $approve = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.invoices.approve', [
                'workspace' => $workspace,
                'invoice' => $invoice,
            ]));

        $approve->assertRedirect();

        $invoice = $invoice->fresh();
        $this->assertNotNull($invoice->internal_approved_at);
        $this->assertSame($owner->getKey(), $invoice->internal_approved_by);

        $send = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.invoices.status.update', [
                'workspace' => $workspace,
                'invoice' => $invoice,
            ]), [
                'status' => 'sent',
            ]);

        $send->assertRedirect();

        $invoice = $invoice->fresh();
        $this->assertSame('sent', $invoice->status);
        $this->assertNotNull($invoice->sent_at);

        $payment = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.invoices.payments.store', [
                'workspace' => $workspace,
                'invoice' => $invoice,
            ]), [
                'amount' => $invoice->total,
                'method' => 'manual_transfer',
                'paid_at' => now()->toDateString(),
                'notes' => 'Transfer confirmed manually.',
            ]);

        $payment->assertRedirect();

        $invoice = $invoice->fresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame($invoice->total, $invoice->paid_amount);
        $this->assertNotNull($invoice->paid_at);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->getKey(),
            'workspace_id' => $workspace->getKey(),
            'status' => 'verified',
            'method' => 'manual_transfer',
        ]);

        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $invoice->getKey(),
            'workspace_id' => $workspace->getKey(),
            'type' => 'income',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.invoices.destroy', [
                'workspace' => $workspace,
                'invoice' => $invoice,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function invoiceContext(): array
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

    protected function createContract(Workspace $workspace, User $owner, Client $client, Project $project): Contract
    {
        return Contract::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Service Contract',
            'status' => 'signed',
            'content' => 'Contract content',
            'signed_at' => now(),
            'signed_by_name' => 'Client PIC',
            'created_by' => $owner->getKey(),
        ]);
    }

    protected function createApprovedQuotation(Workspace $workspace, User $owner, Client $client): Quotation
    {
        $quotation = Quotation::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'number' => 'QUO-202605-0001',
            'title' => 'Approved Retainer Quotation',
            'status' => 'approved',
            'version' => 1,
            'discount_amount' => 0,
            'tax_rate' => 11,
            'dp_percentage' => 30,
            'valid_until' => now()->addDays(14),
            'approval_token' => 'quote-token',
            'approved_at' => now(),
            'approved_by' => $owner->getKey(),
            'created_by' => $owner->getKey(),
        ]);

        $quotation->items()->create([
            'name' => 'Retainer Package',
            'description' => 'Monthly retainer package',
            'category' => 'maintenance',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 200000,
            'discount_amount' => 0,
            'subtotal' => 200000,
            'order_index' => 1,
        ]);

        $quotation->items()->create([
            'name' => 'Setup Fee',
            'description' => 'One-time setup fee',
            'category' => 'development',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 50000,
            'discount_amount' => 0,
            'subtotal' => 50000,
            'order_index' => 2,
        ]);

        return $quotation->fresh()->load('items');
    }
}
