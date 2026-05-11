<?php

namespace Tests\Feature;

use App\Models\ActivityFeed;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TaskPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_task_index_page_returns_task_management_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->taskContext();
        $project = $this->createProject($workspace, $owner, 'Task Board Project');

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'assigned_to' => $owner->getKey(),
            'title' => 'Prepare sprint board',
            'status' => 'in_progress',
            'priority' => 'high',
            'tags' => ['planning', 'frontend'],
            'due_date' => now()->addDays(2),
            'actual_hours' => 3.5,
            'created_by' => $owner->getKey(),
        ]);

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'task_comment',
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'description' => 'Please review @rani before handoff.',
            'metadata' => ['action' => 'comment'],
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.tasks.index', [
                'workspace' => $workspace,
                'status' => 'in_progress',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Tasks/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.status', 'in_progress')
                ->where('tasks.summary.total_tasks', 1)
                ->where('tasks.items.0.title', 'Prepare sprint board')
                ->where('tasks.items.0.project.name', 'Task Board Project')
                ->where('tasks.items.0.counts.comments', 1)
                ->has('filterOptions.projects')
                ->has('filterOptions.assignees')
            );
    }

    public function test_task_can_be_created_with_dependency_and_subtask_context(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->taskContext();
        $member = $this->attachWorkspaceMember($workspace, 'task-member@kantordigital.test', 'Task Member');
        $project = $this->createProject($workspace, $owner, 'Launch Sprint');

        $parent = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Parent Planning',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $dependency = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Dependency Task',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $template = TaskTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'title' => 'QA Checklist',
            'description' => 'Checklist template for review.',
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.tasks.store', $workspace), [
                'project_id' => $project->getKey(),
                'parent_task_id' => $parent->getKey(),
                'assigned_to' => $member->getKey(),
                'title' => 'Execute QA Pass',
                'description' => 'Run regression and capture notes.',
                'status' => 'todo',
                'priority' => 'high',
                'tags' => ['qa', 'launch'],
                'due_date' => now()->addDay()->toDateTimeString(),
                'estimated_hours' => 4,
                'actual_hours' => 0,
                'is_recurring' => true,
                'recurrence_rule' => 'weekly',
                'template_id' => $template->getKey(),
                'dependency_ids' => [$dependency->getKey()],
            ]);

        $response->assertRedirect();

        $task = Task::query()->where('title', 'Execute QA Pass')->firstOrFail();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'project_id' => $project->getKey(),
            'parent_task_id' => $parent->getKey(),
            'assigned_to' => $member->getKey(),
            'template_id' => $template->getKey(),
            'is_recurring' => true,
            'recurrence_rule' => 'weekly',
        ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->getKey(),
            'depends_on_task_id' => $dependency->getKey(),
        ]);
    }

    public function test_task_status_can_be_updated_for_kanban_flow(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->taskContext();
        $project = $this->createProject($workspace, $owner, 'Kanban Task Project');

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Move me',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.tasks.status.update', [
                'workspace' => $workspace,
                'task' => $task,
            ]), [
                'status' => 'review',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'status' => 'review',
        ]);
    }

    public function test_task_time_log_can_be_added_and_rolls_up_actual_hours(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->taskContext();
        $project = $this->createProject($workspace, $owner, 'Time Tracking Project');

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Log my work',
            'status' => 'in_progress',
            'priority' => 'medium',
            'actual_hours' => 0,
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.tasks.time-logs.store', [
                'workspace' => $workspace,
                'task' => $task,
            ]), [
                'started_at' => now()->subHours(2)->toDateTimeString(),
                'ended_at' => now()->toDateTimeString(),
                'notes' => 'Focus block.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('task_time_logs', [
            'task_id' => $task->getKey(),
            'notes' => 'Focus block.',
        ]);

        $this->assertGreaterThan(0, (float) $task->fresh()->actual_hours);
    }

    public function test_task_comment_can_be_added_with_mentions(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->taskContext();
        $project = $this->createProject($workspace, $owner, 'Comment Project');

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Need comment',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.tasks.comments.store', [
                'workspace' => $workspace,
                'task' => $task,
            ]), [
                'content' => 'Please sync with @rani and @budi before closing this task.',
            ]);

        $response->assertRedirect();

        $activity = ActivityFeed::query()
            ->where('subject_type', Task::class)
            ->where('subject_id', $task->getKey())
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame('task_comment', $activity->type);
        $this->assertSame(['rani', 'budi'], $activity->metadata['mentions'] ?? []);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function taskContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
    }

    protected function createProject(Workspace $workspace, User $owner, string $name): Project
    {
        return Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'status' => 'active',
            'created_by' => $owner->getKey(),
        ]);
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
