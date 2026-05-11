<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class MeetingService
{
    public function create(Workspace $workspace, array $data): Meeting
    {
        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $client = $this->resolveClient($workspace, $data['client_id'] ?? null);

        $meeting = Meeting::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project?->getKey(),
            'client_id' => $client?->getKey(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recording_url' => $data['recording_url'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'status' => $data['status'],
            'external_attendees' => $this->normalizeExternalAttendees($data['external_attendees'] ?? []),
            'created_by' => Auth::id(),
        ]);

        $this->syncInternalAttendees($workspace, $meeting, $data['internal_attendee_ids'] ?? []);
        $this->syncActionItems($workspace, $meeting, $project, $data['action_items'] ?? []);
        $this->logActivity($workspace, $meeting, sprintf('Meeting %s berhasil dijadwalkan.', $meeting->title), 'create', 'emerald');

        return $meeting->refresh()->load(['project', 'client', 'attendees.user', 'actionItems.assignee']);
    }

    public function update(Workspace $workspace, Meeting $meeting, array $data): Meeting
    {
        abort_unless($meeting->workspace_id === $workspace->getKey(), 404);

        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $client = $this->resolveClient($workspace, $data['client_id'] ?? null);

        $meeting->update([
            'project_id' => $project?->getKey(),
            'client_id' => $client?->getKey(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recording_url' => $data['recording_url'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'status' => $data['status'],
            'external_attendees' => $this->normalizeExternalAttendees($data['external_attendees'] ?? []),
        ]);

        $this->syncInternalAttendees($workspace, $meeting, $data['internal_attendee_ids'] ?? []);
        $this->syncActionItems($workspace, $meeting, $project, $data['action_items'] ?? []);
        $this->logActivity($workspace, $meeting, sprintf('Meeting %s diperbarui.', $meeting->title), 'update', 'amber');

        return $meeting->refresh()->load(['project', 'client', 'attendees.user', 'actionItems.assignee']);
    }

    public function delete(Workspace $workspace, Meeting $meeting): void
    {
        abort_unless($meeting->workspace_id === $workspace->getKey(), 404);

        $title = $meeting->title;
        $meeting->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'meeting',
            'subject_type' => Meeting::class,
            'subject_id' => null,
            'description' => sprintf('Meeting %s dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    protected function resolveProject(Workspace $workspace, ?string $projectId): ?Project
    {
        if (! $projectId) {
            return null;
        }

        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($projectId);
    }

    protected function resolveClient(Workspace $workspace, ?string $clientId): ?Client
    {
        if (! $clientId) {
            return null;
        }

        return Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($clientId);
    }

    protected function syncInternalAttendees(Workspace $workspace, Meeting $meeting, array $attendeeIds): void
    {
        $ids = collect($attendeeIds)
            ->filter()
            ->map(function (mixed $attendeeId) use ($workspace): string {
                $resolvedId = (string) $attendeeId;
                $exists = $workspace->users()->where('users.id', $resolvedId)->exists();
                abort_unless($exists, 422);

                return $resolvedId;
            })
            ->unique()
            ->values();

        $meeting->attendees()->delete();

        if ($ids->isEmpty()) {
            return;
        }

        $meeting->attendees()->createMany(
            $ids->map(fn (string $userId): array => [
                'user_id' => $userId,
                'is_external' => false,
                'external_name' => null,
                'external_email' => null,
            ])->all()
        );
    }

    protected function normalizeExternalAttendees(array $externalAttendees): array
    {
        return collect($externalAttendees)
            ->filter(fn (mixed $attendee): bool => is_array($attendee))
            ->map(fn (array $attendee): array => [
                'name' => trim((string) ($attendee['name'] ?? '')),
                'email' => trim((string) ($attendee['email'] ?? '')),
            ])
            ->filter(fn (array $attendee): bool => filled($attendee['name']) || filled($attendee['email']))
            ->values()
            ->all();
    }

    protected function syncActionItems(Workspace $workspace, Meeting $meeting, ?Project $project, array $actionItems): void
    {
        if (! $project) {
            return;
        }

        collect($actionItems)
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['title'] ?? null))
            ->map(fn (array $item): array => [
                'title' => trim((string) $item['title']),
                'assigned_to' => filled($item['assigned_to'] ?? null) ? (string) $item['assigned_to'] : null,
                'due_date' => $item['due_date'] ?? null,
                'priority' => filled($item['priority'] ?? null) ? (string) $item['priority'] : 'medium',
            ])
            ->unique(fn (array $item): string => strtolower($item['title']))
            ->each(function (array $item) use ($workspace, $meeting, $project): void {
                if ($item['assigned_to']) {
                    $exists = $workspace->users()->where('users.id', $item['assigned_to'])->exists();
                    abort_unless($exists, 422);
                }

                $exists = $meeting->actionItems()
                    ->where('title', $item['title'])
                    ->exists();

                if ($exists) {
                    return;
                }

                Task::query()->create([
                    'workspace_id' => $workspace->getKey(),
                    'project_id' => $project->getKey(),
                    'meeting_id' => $meeting->getKey(),
                    'assigned_to' => $item['assigned_to'],
                    'title' => $item['title'],
                    'description' => sprintf('Action item dari meeting %s.', $meeting->title),
                    'status' => 'todo',
                    'priority' => $item['priority'],
                    'due_date' => $item['due_date'] ?: $meeting->scheduled_at,
                    'order_index' => (int) (Task::query()
                        ->where('workspace_id', $workspace->getKey())
                        ->where('project_id', $project->getKey())
                        ->max('order_index') ?? 0) + 1,
                    'created_by' => Auth::id(),
                ]);
            });
    }

    protected function logActivity(
        Workspace $workspace,
        Meeting $meeting,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'meeting',
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'CalendarPlus',
                    'update' => 'Pencil',
                    default => 'CalendarRange',
                },
                'color' => $color,
            ],
        ]);
    }
}
