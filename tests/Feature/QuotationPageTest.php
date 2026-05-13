<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class QuotationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_quotation_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->quotationContext();
        $client = $this->createClient($workspace, 'PT Proposal Mantap');
        $lead = $this->createLead($workspace, 'Lead Proposal', 'Lead Proposal Co');

        $quotation = Quotation::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'lead_id' => $lead->getKey(),
            'number' => 'QUO-202605-0001',
            'title' => 'Website Revamp Proposal',
            'status' => 'sent',
            'version' => 1,
            'discount_amount' => 15000,
            'tax_rate' => 11,
            'dp_percentage' => 30,
            'valid_until' => now()->addDays(14),
            'approval_token' => 'token-quotation',
            'created_by' => $owner->getKey(),
        ]);

        $quotation->items()->create([
            'name' => 'Development Package',
            'category' => 'development',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 120000,
            'discount_amount' => 0,
            'subtotal' => 120000,
            'order_index' => 1,
        ]);

        $quotation->items()->create([
            'name' => 'Hosting Setup',
            'category' => 'server_hosting',
            'quantity' => 1,
            'unit' => 'setup',
            'unit_price' => 50000,
            'discount_amount' => 5000,
            'subtotal' => 45000,
            'order_index' => 2,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.quotations.index', [
                'workspace' => $workspace,
                'status' => 'sent',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Quotations/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.status', 'sent')
                ->where('quotations.summary.sent_quotations', 1)
                ->where('quotations.items.0.title', 'Website Revamp Proposal')
                ->where('quotations.items.0.client.name', 'PT Proposal Mantap')
                ->where('quotations.items.0.counts.items', 2)
                ->has('filterOptions.clients')
                ->has('filterOptions.itemCategories', 9)
            );
    }

    public function test_quotation_can_be_created_sent_approved_and_converted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->quotationContext();
        $client = $this->createClient($workspace, 'PT Approval Studio');
        $lead = $this->createLead($workspace, 'Approval Lead', 'Approval Co');

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.quotations.store', $workspace), [
                'client_id' => $client->getKey(),
                'lead_id' => $lead->getKey(),
                'title' => 'Marketing Site Proposal',
                'cover_letter' => 'Intro text',
                'scope_of_work' => "Scope A\nScope B",
                'timeline' => '2 weeks',
                'terms_conditions' => 'Payment terms.',
                'discount_amount' => 10000,
                'tax_rate' => 11,
                'dp_percentage' => 30,
                'valid_until' => now()->addDays(21)->toDateString(),
                'items' => [
                    [
                        'name' => 'Design',
                        'category' => 'design',
                        'quantity' => 1,
                        'unit' => 'paket',
                        'unit_price' => 100000,
                        'discount_amount' => 0,
                    ],
                    [
                        'name' => 'Hosting',
                        'category' => 'server_hosting',
                        'quantity' => 1,
                        'unit' => 'bulan',
                        'unit_price' => 50000,
                        'discount_amount' => 5000,
                    ],
                ],
            ]);

        $create->assertRedirect();

        $quotation = Quotation::query()->where('title', 'Marketing Site Proposal')->firstOrFail();

        $this->assertSame('draft', $quotation->status);
        $this->assertSame(2, $quotation->items()->count());
        $this->assertSame('145000.00', $quotation->subtotal);
        $this->assertSame('14850.00', $quotation->tax_amount);
        $this->assertSame('149850.00', $quotation->total);
        $this->assertSame('44955.00', $quotation->dp_amount);

        $this
            ->actingAs($owner)
            ->post(route('workspace.finance.quotations.send', [
                'workspace' => $workspace,
                'quotation' => $quotation,
            ]))
            ->assertRedirect();

        $quotation = $quotation->fresh();

        $this->assertSame('sent', $quotation->status);
        $this->assertNotNull($quotation->approval_token);

        $this
            ->post(route('quotations.public.decide', [
                'token' => $quotation->approval_token,
            ]), [
                'decision' => 'approved',
            ])
            ->assertOk();

        $this->assertSame('approved', $quotation->fresh()->status);

        $this
            ->actingAs($owner)
            ->post(route('workspace.finance.quotations.convert', [
                'workspace' => $workspace,
                'quotation' => $quotation,
            ]), [
                'create_project' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'workspace_id' => $workspace->getKey(),
            'name' => 'Marketing Site Proposal',
        ]);

        $this->assertDatabaseHas('invoices', [
            'workspace_id' => $workspace->getKey(),
            'quotation_id' => $quotation->getKey(),
            'status' => 'draft',
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function quotationContext(): array
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

    protected function createLead(Workspace $workspace, string $name, string $company): Lead
    {
        return Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'company' => $company,
            'source' => 'website',
        ]);
    }
}
