<?php

namespace Tests\Feature;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\File;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProjectPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_project_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();

        $client = Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => 'PT Orbit Digital',
            'status' => 'active',
            'portal_access' => true,
        ]);

        $template = ProjectTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Web Development',
            'description' => 'Template website company profile.',
            'default_tasks' => [
                ['title' => 'Kickoff meeting', 'priority' => 'medium'],
            ],
        ]);

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'template_id' => $template->getKey(),
            'name' => 'Website Revamp',
            'description' => 'Revamp website utama client.',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'budget' => 25000000,
            'actual_cost' => 5500000,
            'progress' => 40,
            'tags' => ['web-dev', 'launch'],
            'created_by' => $owner->getKey(),
        ]);

        $project->members()->create([
            'user_id' => $owner->getKey(),
            'role' => 'pm',
            'joined_at' => now(),
        ]);

        Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Homepage implementation',
            'status' => 'done',
            'priority' => 'high',
            'created_by' => $owner->getKey(),
        ]);

        File::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'name' => 'homepage-v1.fig',
            'approval_status' => 'pending',
            'version' => 1,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.projects.index', [
                'workspace' => $workspace,
                'status' => 'active',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Projects/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.status', 'active')
                ->where('projects.summary.total_projects', 1)
                ->where('projects.items.0.name', 'Website Revamp')
                ->where('projects.items.0.client.company_name', 'PT Orbit Digital')
                ->where('projects.items.0.pending_approvals', 1)
                ->has('projectTemplates', 1)
                ->has('filterOptions.clients', 1)
                ->has('filterOptions.assignees')
            );
    }

    public function test_project_can_be_created_with_members_and_template_tasks(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();
        $member = $this->attachWorkspaceMember($workspace, 'project-team@kantordigital.test', 'Project Team');

        $client = Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => 'PT Studio Karsa',
            'status' => 'active',
        ]);

        $template = ProjectTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Social Media Retainer',
            'default_tasks' => [
                ['title' => 'Monthly planning', 'priority' => 'medium'],
                ['title' => 'Content production', 'priority' => 'medium'],
            ],
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.projects.store', $workspace), [
                'client_id' => $client->getKey(),
                'template_id' => $template->getKey(),
                'name' => 'IG Retainer Q3',
                'description' => 'Project retainer konten bulanan.',
                'status' => 'planning',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'budget' => 12000000,
                'actual_cost' => 0,
                'tags' => ['retainer', 'social-media'],
                'members' => [
                    ['user_id' => $member->getKey(), 'role' => 'designer'],
                ],
            ]);

        $response->assertRedirect();

        $project = Project::query()->where('name', 'IG Retainer Q3')->firstOrFail();

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'template_id' => $template->getKey(),
            'status' => 'planning',
        ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->getKey(),
            'user_id' => $member->getKey(),
            'role' => 'designer',
        ]);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->getKey(),
            'title' => 'Monthly planning',
        ]);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->getKey(),
            'title' => 'Content production',
        ]);
    }

    public function test_project_can_be_updated(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Old Project Name',
            'status' => 'planning',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.projects.update', [
                'workspace' => $workspace,
                'project' => $project,
            ]), [
                'client_id' => null,
                'template_id' => null,
                'name' => 'Updated Project Name',
                'description' => 'Updated scope.',
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(21)->toDateString(),
                'budget' => 33000000,
                'actual_cost' => 7000000,
                'tags' => ['backend', 'launch'],
                'members' => [],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'name' => 'Updated Project Name',
            'status' => 'active',
            'description' => 'Updated scope.',
        ]);
    }

    public function test_project_show_page_returns_overview_tabs(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();

        $client = Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => 'PT Sagara',
            'status' => 'active',
            'portal_access' => true,
        ]);

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'name' => 'Portal Rollout',
            'status' => 'active',
            'budget' => 40000000,
            'actual_cost' => 15000000,
            'created_by' => $owner->getKey(),
        ]);

        Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Kickoff',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        File::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'name' => 'delivery.zip',
            'approval_status' => 'pending',
            'version' => 1,
        ]);

        Note::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'SOP Launch',
            'type' => 'sop',
            'created_by' => $owner->getKey(),
        ]);

        Meeting::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'client_id' => $client->getKey(),
            'title' => 'Weekly Sync',
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
            'created_by' => $owner->getKey(),
        ]);

        Invoice::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'number' => 'INV-PROJECT-001',
            'status' => 'draft',
            'total' => 12500000,
            'paid_amount' => 0,
            'currency' => 'IDR',
            'created_by' => $owner->getKey(),
        ]);

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'project',
            'subject_type' => Project::class,
            'subject_id' => $project->getKey(),
            'description' => 'Portal rollout kickoff scheduled.',
            'metadata' => ['action' => 'update'],
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.projects.show', [
                'workspace' => $workspace,
                'project' => $project,
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Projects/Show/Overview')
                ->where('project.name', 'Portal Rollout')
                ->where('project.client.company_name', 'PT Sagara')
                ->where('project.counts.pending_approvals', 1)
                ->has('tabs.tasks', 1)
                ->has('tabs.files', 1)
                ->has('tabs.notes', 1)
                ->has('tabs.meetings', 1)
                ->has('tabs.invoices', 1)
                ->has('activities', 1)
            );
    }

    public function test_project_can_be_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Disposable Project',
            'status' => 'planning',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('workspace.projects.destroy', [
                'workspace' => $workspace,
                'project' => $project,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->getKey(),
        ]);
    }

    public function test_project_status_can_be_updated_from_kanban_flow(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->projectContext();

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Kanban Flow Target',
            'status' => 'planning',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.projects.status.update', [
                'workspace' => $workspace,
                'project' => $project,
            ]), [
                'status' => 'active',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_feed', [
            'workspace_id' => $workspace->getKey(),
            'subject_type' => Project::class,
            'subject_id' => $project->getKey(),
            'type' => 'project',
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function projectContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
    }

    protected function attachWorkspaceMember(Workspace $workspace, string $email, string $name): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $role = Role::withoutGlobalScopes()
            ->where('workspace_id', $workspace->getKey())
            ->firstOrFail();

        $workspace->users()->syncWithoutDetaching([
            $user->getKey() => [
                'id' => (string) Str::uuid(),
                'role_id' => $role->getKey(),
                'is_owner' => false,
                'joined_at' => now(),
                'expires_at' => null,
            ],
        ]);

        return $user;
    }
}
