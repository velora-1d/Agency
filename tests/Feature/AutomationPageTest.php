<?php

namespace Tests\Feature;

use App\Models\AutomationRunLog;
use App\Models\AutomationWorkflow;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AutomationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_automation_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Lead Follow Up',
            'trigger_event' => 'lead_created',
            'n8n_workflow_id' => 'lead-follow-up',
            'n8n_webhook_url' => 'https://n8n.example.test/webhook/lead-follow-up',
            'config' => [
                'description' => 'Workflow follow up untuk lead baru.',
                'trigger_type' => 'event',
                'template_key' => 'lead_follow_up',
                'retry_enabled' => true,
                'retry_limit' => 3,
                'conditions' => [
                    ['field' => 'lead.priority', 'operator' => 'equals', 'value' => 'high'],
                ],
                'steps' => [
                    ['type' => 'send_whatsapp', 'label' => 'Send WA', 'target' => 'lead.phone', 'message' => 'Halo lead baru'],
                ],
            ],
            'is_active' => true,
        ]);

        AutomationRunLog::query()->create([
            'workspace_id' => $workspace->getKey(),
            'automation_workflow_id' => $workflow->getKey(),
            'trigger_event' => 'lead_created',
            'status' => 'success',
            'attempt' => 1,
            'message' => 'Workflow berhasil dikirim ke n8n.',
            'payload' => ['event' => 'lead_created'],
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.automation.index', [
                'workspace' => $workspace,
                'status' => 'active',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Automation/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.status', 'active')
                ->where('automation.summary.total_workflows', 1)
                ->where('automation.summary.success_runs', 1)
                ->where('automation.items.0.name', 'Lead Follow Up')
                ->where('automation.items.0.trigger_label', 'Lead masuk')
                ->where('automation.logs.0.status', 'success')
                ->has('automation.templates')
                ->has('automation.options.trigger_events')
            );
    }

    public function test_automation_workflow_can_be_created(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.automation.store', $workspace), [
                'name' => 'Invoice Reminder',
                'description' => 'Reminder invoice due via WA dan email.',
                'trigger_event' => 'invoice_due',
                'trigger_type' => 'event',
                'schedule_expression' => null,
                'n8n_workflow_id' => 'invoice-reminder',
                'n8n_webhook_url' => 'https://n8n.example.test/webhook/invoice-reminder',
                'template_key' => 'invoice_reminder',
                'retry_enabled' => true,
                'retry_limit' => 2,
                'is_active' => true,
                'conditions' => [
                    ['field' => 'invoice.status', 'operator' => 'not_equals', 'value' => 'paid'],
                ],
                'steps' => [
                    ['type' => 'send_email', 'label' => 'Email Reminder', 'target' => 'client.email', 'message' => 'Invoice due reminder'],
                    ['type' => 'send_whatsapp', 'label' => 'WA Reminder', 'target' => 'client.phone', 'message' => 'Invoice due reminder'],
                ],
            ]);

        $response->assertRedirect();

        $workflow = AutomationWorkflow::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('name', 'Invoice Reminder')
            ->firstOrFail();

        $this->assertSame('invoice_due', $workflow->trigger_event);
        $this->assertTrue($workflow->is_active);
        $this->assertSame('event', $workflow->config['trigger_type'] ?? null);
        $this->assertSame('invoice_reminder', $workflow->config['template_key'] ?? null);
        $this->assertCount(2, $workflow->config['steps'] ?? []);
    }

    public function test_automation_workflow_can_be_updated(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Old Workflow',
            'trigger_event' => 'lead_created',
            'config' => [
                'description' => 'Old description',
                'trigger_type' => 'event',
                'retry_enabled' => false,
                'retry_limit' => 0,
                'conditions' => [],
                'steps' => [
                    ['type' => 'notify_team', 'label' => 'Notify', 'target' => 'sales_team', 'message' => 'Lead masuk'],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.automation.update', [
                'workspace' => $workspace,
                'workflow' => $workflow,
            ]), [
                'name' => 'Task Complete Notify',
                'description' => 'Notify team ketika task selesai.',
                'trigger_event' => 'task_completed',
                'trigger_type' => 'event',
                'schedule_expression' => null,
                'n8n_workflow_id' => 'task-complete-notify',
                'n8n_webhook_url' => 'https://n8n.example.test/webhook/task-complete-notify',
                'template_key' => 'task_complete_notify',
                'retry_enabled' => false,
                'retry_limit' => 0,
                'is_active' => false,
                'conditions' => [],
                'steps' => [
                    ['type' => 'notify_team', 'label' => 'Notify Project Team', 'target' => 'project_team', 'message' => 'Task selesai'],
                    ['type' => 'create_task', 'label' => 'Follow Up', 'target' => 'backlog', 'message' => 'Buat follow up task'],
                ],
            ]);

        $response->assertRedirect();

        $workflow->refresh();

        $this->assertSame('Task Complete Notify', $workflow->name);
        $this->assertSame('task_completed', $workflow->trigger_event);
        $this->assertFalse($workflow->is_active);
        $this->assertSame('task_complete_notify', $workflow->config['template_key'] ?? null);
        $this->assertCount(2, $workflow->config['steps'] ?? []);
    }

    public function test_automation_workflow_can_be_toggled(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Toggle Workflow',
            'trigger_event' => 'payment_received',
            'config' => [
                'trigger_type' => 'event',
                'conditions' => [],
                'steps' => [
                    ['type' => 'update_status', 'label' => 'Update', 'target' => 'invoice', 'message' => 'Set paid'],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.automation.toggle', [
                'workspace' => $workspace,
                'workflow' => $workflow,
            ]), [
                'is_active' => false,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('automation_workflows', [
            'id' => $workflow->getKey(),
            'is_active' => false,
        ]);
    }

    public function test_automation_run_test_creates_log_entry(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Run Test Workflow',
            'trigger_event' => 'form_submitted',
            'config' => [
                'trigger_type' => 'event',
                'conditions' => [],
                'steps' => [
                    ['type' => 'create_task', 'label' => 'Create Intake Task', 'target' => 'ops', 'message' => 'Task baru'],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.automation.run-test', [
                'workspace' => $workspace,
                'workflow' => $workflow,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('automation_run_logs', [
            'workspace_id' => $workspace->getKey(),
            'automation_workflow_id' => $workflow->getKey(),
            'trigger_event' => 'form_submitted',
            'status' => 'success',
        ]);
    }

    public function test_automation_workflow_can_be_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->automationContext();

        $workflow = AutomationWorkflow::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Delete Workflow',
            'trigger_event' => 'support_ticket_created',
            'config' => [
                'trigger_type' => 'event',
                'conditions' => [],
                'steps' => [
                    ['type' => 'notify_team', 'label' => 'Notify Support', 'target' => 'support_team', 'message' => 'Ticket baru'],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('workspace.automation.destroy', [
                'workspace' => $workspace,
                'workflow' => $workflow,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('automation_workflows', [
            'id' => $workflow->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function automationContext(): array
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

        return [$owner, $workspace];
    }
}
