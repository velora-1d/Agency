<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteFolder;
use App\Models\Project;
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

class NotePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_note_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->noteContext();
        $project = $this->createProject($workspace, $owner, 'SOP Project');
        $folder = NoteFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Operations',
        ]);

        $note = Note::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'folder_id' => $folder->getKey(),
            'title' => 'Launch SOP',
            'content' => 'Checklist launch and QA notes.',
            'type' => 'sop',
            'is_private' => false,
            'version' => 2,
            'created_by' => $owner->getKey(),
        ]);

        $note->revisions()->create([
            'title' => 'Launch SOP',
            'content' => 'Checklist launch and QA notes.',
            'version' => 2,
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'sop_note_id' => $note->getKey(),
            'title' => 'Run launch QA',
            'status' => 'todo',
            'priority' => 'high',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.notes.index', [
                'workspace' => $workspace,
                'type' => 'sop',
                'note' => $note->getKey(),
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Notes/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.type', 'sop')
                ->where('notes.selected_id', $note->getKey())
                ->where('notes.summary.total_notes', 1)
                ->where('notes.items.0.title', 'Launch SOP')
                ->where('notes.items.0.folder.name', 'Operations')
                ->where('notes.items.0.project.name', 'SOP Project')
                ->where('notes.items.0.counts.linked_tasks', 1)
                ->has('folders', 1)
                ->has('filterOptions.projects')
                ->has('filterOptions.tasks')
            );

        $this->assertSame($note->getKey(), $task->fresh()->sop_note_id);
    }

    public function test_note_can_be_created_with_folder_and_linked_tasks(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->noteContext();
        $project = $this->createProject($workspace, $owner, 'Knowledge Base Project');
        $folder = NoteFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Playbooks',
        ]);

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Follow SOP',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.notes.store', $workspace), [
                'project_id' => $project->getKey(),
                'folder_id' => $folder->getKey(),
                'title' => 'QA Playbook',
                'content' => "## Scope\n- [ ] Regression",
                'type' => 'sop',
                'is_private' => false,
                'linked_task_ids' => [$task->getKey()],
            ]);

        $response->assertRedirect();

        $note = Note::query()->where('title', 'QA Playbook')->firstOrFail();

        $this->assertDatabaseHas('notes', [
            'id' => $note->getKey(),
            'folder_id' => $folder->getKey(),
            'project_id' => $project->getKey(),
            'type' => 'sop',
        ]);

        $this->assertDatabaseHas('note_revisions', [
            'note_id' => $note->getKey(),
            'version' => 1,
        ]);

        $this->assertSame($note->getKey(), $task->fresh()->sop_note_id);
    }

    public function test_note_update_increments_version_and_relinks_tasks(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->noteContext();
        $project = $this->createProject($workspace, $owner, 'Docs Refactor');

        $note = Note::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Old SOP',
            'content' => 'Old content',
            'type' => 'sop',
            'is_private' => true,
            'version' => 1,
            'created_by' => $owner->getKey(),
        ]);

        $note->revisions()->create([
            'title' => 'Old SOP',
            'content' => 'Old content',
            'version' => 1,
            'created_by' => $owner->getKey(),
            'created_at' => now()->subMinute(),
        ]);

        $oldTask = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'sop_note_id' => $note->getKey(),
            'title' => 'Old linked task',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $newTask = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'New linked task',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.notes.update', [
                'workspace' => $workspace,
                'note' => $note,
            ]), [
                'project_id' => $project->getKey(),
                'folder_id' => null,
                'title' => 'Updated SOP',
                'content' => 'Updated content',
                'type' => 'template',
                'is_private' => false,
                'linked_task_ids' => [$newTask->getKey()],
            ]);

        $response->assertRedirect();

        $note = $note->fresh();

        $this->assertSame(2, $note->version);
        $this->assertDatabaseHas('note_revisions', [
            'note_id' => $note->getKey(),
            'version' => 2,
        ]);
        $this->assertNull($oldTask->fresh()->sop_note_id);
        $this->assertSame($note->getKey(), $newTask->fresh()->sop_note_id);
    }

    public function test_folder_can_be_created_updated_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->noteContext();

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.notes.folders.store', $workspace), [
                'name' => 'Guidelines',
            ]);

        $create->assertRedirect();

        $folder = NoteFolder::query()->where('name', 'Guidelines')->firstOrFail();

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.notes.folders.update', [
                'workspace' => $workspace,
                'folder' => $folder,
            ]), [
                'name' => 'Updated Guidelines',
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('note_folders', [
            'id' => $folder->getKey(),
            'name' => 'Updated Guidelines',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.notes.folders.destroy', [
                'workspace' => $workspace,
                'folder' => $folder,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('note_folders', [
            'id' => $folder->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function noteContext(): array
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
