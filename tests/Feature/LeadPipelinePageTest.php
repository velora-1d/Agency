<?php

namespace Tests\Feature;

use App\Models\ActivityFeed;
use App\Models\AutomationWorkflow;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LeadPipelinePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_lead_pipeline_page_returns_filtered_crm_payload(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'PT Aurora Digital',
            'company' => 'Aurora Digital',
            'email' => 'hello@aurora.test',
            'phone' => '08123456789',
            'source' => 'instagram',
            'estimated_value' => 25000000,
            'priority' => 'high',
            'assigned_to' => $owner->getKey(),
            'ai_score' => 88,
            'notes' => 'Inbound lead from campaign.',
        ]);

        Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'PT Offline Referral',
            'source' => 'referral',
            'estimated_value' => 10000000,
            'priority' => 'medium',
        ]);

        Form::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Website Inquiry',
            'fields' => [['name' => 'name', 'type' => 'text']],
            'auto_create_lead' => true,
        ]);

        AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Lead WhatsApp Follow Up',
            'trigger_event' => 'lead_created',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.crm.leads.index', [
                'workspace' => $workspace,
                'source' => 'instagram',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('CRM/Leads/Pipeline')
                ->where('workspace.slug', 'velora')
                ->where('filters.source', 'instagram')
                ->where('crm.summary.total_leads', 1)
                ->where('crm.summary.forms_auto_create_enabled', 1)
                ->where('crm.summary.auto_whatsapp_enabled', true)
                ->where('crm.table.0.name', 'PT Aurora Digital')
                ->where('crm.table.0.ai_score', 88)
                ->has('crm.pipelines', 2)
                ->has('filterOptions.pipelines')
                ->has('filterOptions.stages')
            );
    }

    public function test_leads_export_returns_csv_file(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Export Lead',
            'source' => 'website form',
            'estimated_value' => 5000000,
            'priority' => 'high',
            'assigned_to' => $owner->getKey(),
            'ai_score' => 75,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.crm.leads.export', $workspace));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Export Lead', $content);
        $this->assertStringContainsString('Estimated Value', $content);
    }

    public function test_lead_detail_page_returns_notes_and_activity_history(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $lead = Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Lead Detail Target',
            'company' => 'Detail Co',
            'source' => 'referral',
            'estimated_value' => 15000000,
            'priority' => 'medium',
            'assigned_to' => $owner->getKey(),
            'ai_score' => 64,
            'notes' => 'Needs proposal update next week.',
        ]);

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'lead',
            'subject_type' => Lead::class,
            'subject_id' => $lead->getKey(),
            'description' => 'Updated pricing discussion.',
            'metadata' => ['action' => 'update'],
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.crm.leads.show', [
                'workspace' => $workspace,
                'lead' => $lead,
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('CRM/Leads/Show')
                ->where('lead.name', 'Lead Detail Target')
                ->where('lead.notes', 'Needs proposal update next week.')
                ->has('activities', 1)
                ->where('activities.0.description', 'Updated pricing discussion.')
            );
    }

    public function test_lead_can_be_created_from_crm_menu(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.crm.leads.store', $workspace), [
                'pipeline_id' => $pipeline->getKey(),
                'stage_id' => $stage->getKey(),
                'name' => 'Created From CRM',
                'company' => 'Launch Labs',
                'email' => 'launch@labs.test',
                'phone' => '0811001100',
                'source' => 'website form',
                'estimated_value' => 22000000,
                'priority' => 'high',
                'assigned_to' => $owner->getKey(),
                'notes' => 'Created via modal.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'workspace_id' => $workspace->getKey(),
            'name' => 'Created From CRM',
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'priority' => 'high',
        ]);

        $lead = Lead::query()->where('name', 'Created From CRM')->firstOrFail();
        $this->assertGreaterThanOrEqual(60, $lead->ai_score);
    }

    public function test_lead_can_be_moved_between_stages(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $nextStage = PipelineStage::query()
            ->where('pipeline_id', $pipeline->getKey())
            ->where('order_index', 2)
            ->firstOrFail();

        $lead = Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Move Me',
            'priority' => 'medium',
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.move-stage', [
                'workspace' => $workspace,
                'lead' => $lead,
            ]), [
                'pipeline_id' => $pipeline->getKey(),
                'stage_id' => $nextStage->getKey(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->getKey(),
            'stage_id' => $nextStage->getKey(),
        ]);
    }

    public function test_pipeline_with_custom_stages_can_be_created_from_leads_menu(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->leadContext();

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.crm.leads.pipelines.store', $workspace), [
                'name' => 'Social Media Retainer',
                'description' => 'Pipeline for monthly social media retainers.',
                'is_default' => false,
                'stages' => [
                    ['name' => 'New', 'color' => '#94A3B8', 'is_won' => false, 'is_lost' => false],
                    ['name' => 'Proposal', 'color' => '#8B5CF6', 'is_won' => false, 'is_lost' => false],
                    ['name' => 'Won', 'color' => '#10B981', 'is_won' => true, 'is_lost' => false],
                ],
            ]);

        $response->assertRedirect();

        $pipeline = Pipeline::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('name', 'Social Media Retainer')
            ->firstOrFail();

        $this->assertSame(3, $pipeline->stages()->count());
        $this->assertDatabaseHas('pipeline_stages', [
            'pipeline_id' => $pipeline->getKey(),
            'name' => 'Won',
            'is_won' => true,
        ]);
    }

    public function test_existing_pipeline_and_stages_can_be_updated(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.pipelines.update', [
                'workspace' => $workspace,
                'pipeline' => $pipeline,
            ]), [
                'name' => 'Web Dev Premium',
                'description' => 'Updated pipeline description.',
                'is_default' => true,
                'stages' => [
                    [
                        'id' => $stage->getKey(),
                        'name' => 'Qualified',
                        'color' => '#2563EB',
                        'is_won' => false,
                        'is_lost' => false,
                    ],
                    [
                        'name' => 'Closed Won',
                        'color' => '#10B981',
                        'is_won' => true,
                        'is_lost' => false,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->getKey(),
            'name' => 'Web Dev Premium',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->getKey(),
            'name' => 'Qualified',
            'order_index' => 1,
        ]);

        $this->assertDatabaseHas('pipeline_stages', [
            'pipeline_id' => $pipeline->getKey(),
            'name' => 'Closed Won',
            'order_index' => 2,
            'is_won' => true,
        ]);
    }

    public function test_form_submission_auto_creates_lead_when_enabled(): void
    {
        $this->seed();

        [, $workspace] = $this->leadContext();

        $form = Form::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Website Inquiry',
            'fields' => [['name' => 'name', 'type' => 'text']],
            'auto_create_lead' => true,
        ]);

        $submission = FormSubmission::query()->create([
            'form_id' => $form->getKey(),
            'data' => [
                'name' => 'Nadia Putri',
                'email' => 'nadia@example.test',
                'phone' => '081234567890',
                'company' => 'Studio Nadia',
                'budget' => '18000000',
                'message' => 'Need website redesign proposal.',
            ],
            'ip_address' => '127.0.0.1',
            'submitted_at' => now(),
        ]);

        $submission->refresh();
        $lead = Lead::query()->findOrFail($submission->lead_id);

        $this->assertNotNull($submission->lead_id);
        $this->assertSame('Nadia Putri', $lead->name);
        $this->assertSame('website form', $lead->source);
        $this->assertSame('Studio Nadia', $lead->company);
        $this->assertSame('18000000.00', $lead->estimated_value);
        $this->assertGreaterThan(0, $lead->ai_score);
        $this->assertSame(1, $form->fresh()->submission_count);
    }

    public function test_form_submission_does_not_create_lead_when_disabled(): void
    {
        $this->seed();

        [, $workspace] = $this->leadContext();

        $form = Form::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Manual Intake',
            'fields' => [['name' => 'email', 'type' => 'email']],
            'auto_create_lead' => false,
        ]);

        $submission = FormSubmission::query()->create([
            'form_id' => $form->getKey(),
            'data' => [
                'email' => 'noauto@example.test',
            ],
            'ip_address' => '127.0.0.1',
            'submitted_at' => now(),
        ]);

        $submission->refresh();

        $this->assertNull($submission->lead_id);
        $this->assertDatabaseMissing('leads', [
            'email' => 'noauto@example.test',
        ]);
    }

    public function test_lead_automation_can_be_enabled_from_crm_menu(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->leadContext();

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.automation.update', $workspace), [
                'enabled' => true,
                'workflow_name' => 'Lead WA Follow Up',
                'webhook_url' => 'https://n8n.example.test/webhook/lead-created',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('automation_workflows', [
            'workspace_id' => $workspace->getKey(),
            'trigger_event' => 'lead_created',
            'name' => 'Lead WA Follow Up',
            'n8n_webhook_url' => 'https://n8n.example.test/webhook/lead-created',
            'is_active' => true,
        ]);

        $this->assertSame('https://n8n.example.test/webhook/lead-created', $workspace->fresh()->n8n_webhook_url);
    }

    public function test_lead_automation_can_be_disabled_from_crm_menu(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->leadContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Lead WA Follow Up',
            'trigger_event' => 'lead_created',
            'n8n_webhook_url' => 'https://n8n.example.test/webhook/lead-created',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.automation.update', $workspace), [
                'enabled' => false,
                'workflow_name' => 'Lead WA Follow Up',
                'webhook_url' => 'https://n8n.example.test/webhook/lead-created',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('automation_workflows', [
            'id' => $workflow->getKey(),
            'is_active' => false,
        ]);
    }

    public function test_lead_notes_can_be_updated_from_detail_page(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $lead = Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Notes Target',
            'priority' => 'medium',
            'notes' => 'Old notes',
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.notes.update', [
                'workspace' => $workspace,
                'lead' => $lead,
            ]), [
                'notes' => 'Updated notes from detail page.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->getKey(),
            'notes' => 'Updated notes from detail page.',
        ]);
    }

    public function test_manual_lead_activity_can_be_added_from_detail_page(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $lead = Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Activity Target',
            'priority' => 'medium',
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.crm.leads.activities.store', [
                'workspace' => $workspace,
                'lead' => $lead,
            ]), [
                'content' => 'Manual follow up via WhatsApp.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_feed', [
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'subject_type' => Lead::class,
            'subject_id' => $lead->getKey(),
            'description' => 'Manual follow up via WhatsApp.',
        ]);

        $activity = ActivityFeed::query()
            ->where('subject_id', $lead->getKey())
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame('note', $activity->metadata['action'] ?? null);
    }

    public function test_pipeline_can_be_deleted_without_deleting_leads(): void
    {
        $this->seed();

        [$owner, $workspace, $pipeline, $stage] = $this->leadContext();

        $replacementPipeline = Pipeline::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Social Media',
            'description' => 'Replacement default pipeline.',
            'is_default' => false,
        ]);

        PipelineStage::query()->create([
            'pipeline_id' => $replacementPipeline->getKey(),
            'name' => 'New',
            'order_index' => 1,
            'color' => '#94A3B8',
            'is_won' => false,
            'is_lost' => false,
        ]);

        $lead = Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipeline->getKey(),
            'stage_id' => $stage->getKey(),
            'name' => 'Pipeline Delete Target',
            'priority' => 'medium',
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('workspace.crm.leads.pipelines.destroy', [
                'workspace' => $workspace,
                'pipeline' => $pipeline,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('pipelines', [
            'id' => $pipeline->getKey(),
        ]);

        $this->assertNull($lead->fresh()->pipeline_id);
        $this->assertNull($lead->fresh()->stage_id);

        $this->assertNotNull(
            Pipeline::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('is_default', true)
                ->first()
        );
    }

    public function test_lead_automation_can_store_enabled_admin_restrictions(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->leadContext();

        $manager = User::query()->create([
            'name' => 'CRM Manager',
            'email' => 'crm-manager@kantordigital.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $ownerRole = Role::withoutGlobalScopes()
            ->where('workspace_id', $workspace->getKey())
            ->where('slug', 'owner')
            ->firstOrFail();

        $workspace->users()->syncWithoutDetaching([
            $manager->getKey() => [
                'id' => (string) Str::uuid(),
                'role_id' => $ownerRole->getKey(),
                'is_owner' => false,
                'joined_at' => now(),
                'expires_at' => null,
            ],
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.crm.leads.automation.update', $workspace), [
                'enabled' => true,
                'workflow_name' => 'Lead WA Follow Up',
                'webhook_url' => 'https://n8n.example.test/webhook/lead-created',
                'enabled_user_ids' => [$manager->getKey()],
            ]);

        $response->assertRedirect();

        $workflow = AutomationWorkflow::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('trigger_event', 'lead_created')
            ->firstOrFail();

        $this->assertSame([$manager->getKey()], $workflow->config['enabled_user_ids'] ?? []);
    }

    /**
     * @return array{0: User, 1: Workspace, 2: Pipeline, 3: PipelineStage}
     */
    protected function leadContext(): array
    {
        $workspace = Workspace::query()->firstOrCreate(
            ['slug' => 'velora'],
            [
                'name' => 'Velora',
                'primary_color' => '#F59E0B',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'language' => 'id',
                'storage_quota_gb' => 50,
            ],
        );

        $owner = User::query()->firstOrCreate(
            ['email' => 'owner@kantordigital.test'],
            [
                'name' => 'Kantor Digital Owner',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $ownerRole = Role::withoutGlobalScopes()->firstOrCreate(
            [
                'workspace_id' => $workspace->getKey(),
                'slug' => 'owner',
            ],
            [
                'name' => 'Owner',
                'description' => 'Workspace owner',
                'is_default' => true,
            ],
        );

        $workspace->users()->syncWithoutDetaching([
            $owner->getKey() => [
                'id' => (string) Str::uuid(),
                'role_id' => $ownerRole->getKey(),
                'is_owner' => true,
                'joined_at' => now(),
                'expires_at' => null,
            ],
        ]);

        $pipeline = Pipeline::query()->firstOrCreate(
            [
                'workspace_id' => $workspace->getKey(),
                'name' => 'Web Development',
            ],
            [
                'description' => 'Pipeline for website and application projects.',
                'is_default' => true,
            ],
        );

        $stage = PipelineStage::query()->firstOrCreate(
            [
                'pipeline_id' => $pipeline->getKey(),
                'order_index' => 1,
            ],
            [
                'name' => 'New',
                'color' => '#94A3B8',
                'is_won' => false,
                'is_lost' => false,
            ],
        );

        PipelineStage::query()->firstOrCreate(
            [
                'pipeline_id' => $pipeline->getKey(),
                'order_index' => 2,
            ],
            [
                'name' => 'Contacted',
                'color' => '#0EA5E9',
                'is_won' => false,
                'is_lost' => false,
            ],
        );

        return [$owner, $workspace, $pipeline, $stage];
    }
}
