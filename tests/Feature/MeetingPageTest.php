<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Meeting;
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

class MeetingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_meeting_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->meetingContext();
        $project = $this->createProject($workspace, $owner, 'Launch Website');
        $client = $this->createClient($workspace, 'PT Aurora Media');

        $meeting = Meeting::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'client_id' => $client->getKey(),
            'title' => 'Kickoff Sync',
            'agenda' => 'Project alignment and milestone review.',
            'notes' => 'Catat dependency dan output meeting.',
            'meeting_url' => 'https://meet.google.com/kickoff-sync',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'external_attendees' => [
                ['name' => 'Dina Client', 'email' => 'dina@aurora.test'],
            ],
            'created_by' => $owner->getKey(),
        ]);

        $meeting->attendees()->create([
            'user_id' => $owner->getKey(),
            'is_external' => false,
        ]);

        Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'meeting_id' => $meeting->getKey(),
            'title' => 'Send kickoff summary',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.meetings.index', [
                'workspace' => $workspace,
                'status' => 'scheduled',
                'meeting' => $meeting->getKey(),
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Meetings/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.status', 'scheduled')
                ->where('meetings.selected_id', $meeting->getKey())
                ->where('meetings.summary.total_meetings', 1)
                ->where('meetings.items.0.title', 'Kickoff Sync')
                ->where('meetings.items.0.project.name', 'Launch Website')
                ->where('meetings.items.0.client.name', 'PT Aurora Media')
                ->where('meetings.items.0.counts.action_items', 1)
                ->has('filterOptions.projects')
                ->has('filterOptions.clients')
                ->has('filterOptions.users')
            );
    }

    public function test_meeting_can_be_created_with_attendees_and_action_items(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->meetingContext();
        $member = $this->attachWorkspaceMember($workspace, 'facilitator@kantordigital.test', 'Facilitator');
        $project = $this->createProject($workspace, $owner, 'Brand Sprint');
        $client = $this->createClient($workspace, 'PT Cipta Brand');

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.meetings.store', $workspace), [
                'project_id' => $project->getKey(),
                'client_id' => $client->getKey(),
                'title' => 'Brand Workshop',
                'description' => 'Workshop untuk menyamakan arah visual.',
                'agenda' => 'Moodboard, tone, approval path.',
                'notes' => 'Hasil meeting dipakai untuk sprint task.',
                'meeting_url' => 'https://zoom.us/j/brand-workshop',
                'recording_url' => 'https://drive.test/recording-brand-workshop',
                'scheduled_at' => now()->addDays(2)->toDateTimeString(),
                'duration_minutes' => 90,
                'status' => 'scheduled',
                'internal_attendee_ids' => [$owner->getKey(), $member->getKey()],
                'external_attendees' => [
                    ['name' => 'Bima Client', 'email' => 'bima@brand.test'],
                ],
                'action_items' => [
                    [
                        'title' => 'Draft creative direction',
                        'assigned_to' => $member->getKey(),
                        'due_date' => now()->addDays(4)->toDateTimeString(),
                        'priority' => 'high',
                    ],
                ],
            ]);

        $response->assertRedirect();

        $meeting = Meeting::query()->where('title', 'Brand Workshop')->firstOrFail();

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->getKey(),
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'client_id' => $client->getKey(),
            'status' => 'scheduled',
        ]);

        $this->assertCount(2, $meeting->fresh()->attendees);
        $this->assertSame('Bima Client', $meeting->fresh()->external_attendees[0]['name'] ?? null);

        $this->assertDatabaseHas('tasks', [
            'meeting_id' => $meeting->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Draft creative direction',
            'assigned_to' => $member->getKey(),
            'priority' => 'high',
        ]);
    }

    public function test_meeting_can_be_updated_and_attendees_are_synced(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->meetingContext();
        $member = $this->attachWorkspaceMember($workspace, 'producer@kantordigital.test', 'Producer');
        $project = $this->createProject($workspace, $owner, 'Retainer Ops');

        $meeting = Meeting::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Weekly Ops',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 45,
            'status' => 'scheduled',
            'created_by' => $owner->getKey(),
        ]);

        $meeting->attendees()->create([
            'user_id' => $owner->getKey(),
            'is_external' => false,
        ]);

        $response = $this
            ->actingAs($owner)
            ->patch(route('workspace.meetings.update', [
                'workspace' => $workspace,
                'meeting' => $meeting,
            ]), [
                'project_id' => $project->getKey(),
                'client_id' => null,
                'title' => 'Weekly Ops Review',
                'description' => 'Review capacity dan blocker.',
                'agenda' => 'Capacity, blockers, handoff.',
                'notes' => 'Perlu follow up asset.',
                'meeting_url' => 'https://meet.google.com/weekly-ops',
                'recording_url' => null,
                'scheduled_at' => now()->addDays(3)->toDateTimeString(),
                'duration_minutes' => 60,
                'status' => 'completed',
                'internal_attendee_ids' => [$member->getKey()],
                'external_attendees' => [
                    ['name' => 'Nadia Client', 'email' => 'nadia@ops.test'],
                ],
                'action_items' => [],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->getKey(),
            'title' => 'Weekly Ops Review',
            'status' => 'completed',
            'duration_minutes' => 60,
        ]);

        $meeting = $meeting->fresh()->load('attendees');

        $this->assertCount(1, $meeting->attendees);
        $this->assertSame($member->getKey(), $meeting->attendees->first()?->user_id);
        $this->assertSame('Nadia Client', $meeting->external_attendees[0]['name'] ?? null);
    }

    public function test_meeting_can_be_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->meetingContext();
        $project = $this->createProject($workspace, $owner, 'Cleanup Ops');

        $meeting = Meeting::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'title' => 'Delete Me',
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
            'created_by' => $owner->getKey(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('workspace.meetings.destroy', [
                'workspace' => $workspace,
                'meeting' => $meeting,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('meetings', [
            'id' => $meeting->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function meetingContext(): array
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

    protected function createClient(Workspace $workspace, string $companyName): Client
    {
        return Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => $companyName,
            'status' => 'active',
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
