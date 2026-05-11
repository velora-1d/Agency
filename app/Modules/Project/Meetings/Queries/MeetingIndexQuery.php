<?php

namespace App\Modules\Project\Meetings\Queries;

use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MeetingIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        $meetings = $this->meetingQuery($workspace, $filters)->get();

        return [
            'meetings' => [
                'summary' => $this->buildSummary($meetings),
                'items' => $meetings->map(fn (Meeting $meeting): array => $this->transformMeeting($meeting))->values()->all(),
                'selected_id' => $filters['meeting'],
            ],
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'client' => $normalize($input['client'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'meeting' => $normalize($input['meeting'] ?? null),
        ];
    }

    protected function meetingQuery(Workspace $workspace, array $filters): Builder
    {
        return Meeting::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'project:id,name',
                'client:id,company_name',
                'attendees.user:id,name',
                'actionItems:id,workspace_id,project_id,meeting_id,assigned_to,title,status,priority,due_date',
                'actionItems.assignee:id,name',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('agenda', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['client'], fn (Builder $query, string $clientId) => $query->where('client_id', $clientId))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('scheduled_at')
            ->orderByDesc('created_at');
    }

    protected function buildSummary(Collection $meetings): array
    {
        $today = now();

        return [
            'total_meetings' => $meetings->count(),
            'today_meetings' => $meetings->filter(fn (Meeting $meeting): bool => $meeting->scheduled_at?->isSameDay($today) ?? false)->count(),
            'upcoming_meetings' => $meetings->filter(fn (Meeting $meeting): bool => $meeting->status === 'scheduled' && ($meeting->scheduled_at?->isFuture() ?? false))->count(),
            'completed_meetings' => $meetings->where('status', 'completed')->count(),
            'action_items' => $meetings->sum(fn (Meeting $meeting): int => $meeting->actionItems->count()),
            'open_action_items' => $meetings->sum(fn (Meeting $meeting): int => $meeting->actionItems->where('status', '!=', 'done')->count()),
        ];
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])
                ->values()
                ->all(),
            'clients' => Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Client $client): array => [
                    'id' => $client->getKey(),
                    'name' => $client->company_name,
                ])
                ->values()
                ->all(),
            'users' => $workspace->users()
                ->select('users.id', 'users.name')
                ->orderBy('users.name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])
                ->values()
                ->all(),
            'statuses' => [
                ['value' => 'scheduled', 'label' => 'Scheduled'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'priorities' => [
                ['value' => 'low', 'label' => 'Low'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'urgent', 'label' => 'Urgent'],
            ],
        ];
    }

    protected function transformMeeting(Meeting $meeting): array
    {
        $scheduledAt = $meeting->scheduled_at;
        $internalAttendees = $meeting->attendees
            ->filter(fn ($attendee): bool => ! $attendee->is_external && $attendee->user !== null)
            ->map(fn ($attendee): array => [
                'id' => $attendee->user->getKey(),
                'name' => $attendee->user->name,
            ])
            ->values()
            ->all();

        $externalAttendees = collect($meeting->external_attendees ?? [])
            ->map(fn (array $attendee): array => [
                'name' => (string) ($attendee['name'] ?? ''),
                'email' => (string) ($attendee['email'] ?? ''),
            ])
            ->filter(fn (array $attendee): bool => filled($attendee['name']) || filled($attendee['email']))
            ->values()
            ->all();

        return [
            'id' => $meeting->getKey(),
            'title' => $meeting->title,
            'description' => $meeting->description,
            'agenda' => $meeting->agenda,
            'notes' => $meeting->notes,
            'meeting_url' => $meeting->meeting_url,
            'recording_url' => $meeting->recording_url,
            'scheduled_at' => $scheduledAt?->toIso8601String(),
            'scheduled_at_label' => $scheduledAt?->format('d M Y H:i'),
            'scheduled_at_human' => $scheduledAt?->diffForHumans(),
            'duration_minutes' => $meeting->duration_minutes,
            'status' => $meeting->status,
            'status_label' => $this->statusLabel($meeting->status),
            'project_id' => $meeting->project_id,
            'client_id' => $meeting->client_id,
            'project' => $meeting->project ? [
                'id' => $meeting->project->getKey(),
                'name' => $meeting->project->name,
            ] : null,
            'client' => $meeting->client ? [
                'id' => $meeting->client->getKey(),
                'name' => $meeting->client->company_name,
            ] : null,
            'internal_attendees' => $internalAttendees,
            'external_attendees' => $externalAttendees,
            'participant_summary' => $this->participantSummary($internalAttendees, $externalAttendees),
            'action_items' => $meeting->actionItems->map(fn (Task $task): array => [
                'id' => $task->getKey(),
                'title' => $task->title,
                'status' => $task->status,
                'status_label' => $this->taskStatusLabel($task->status),
                'priority' => $task->priority,
                'priority_label' => ucfirst($task->priority),
                'due_date_label' => $task->due_date?->format('d M Y H:i'),
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->getKey(),
                    'name' => $task->assignee->name,
                ] : null,
            ])->values()->all(),
            'counts' => [
                'internal_attendees' => count($internalAttendees),
                'external_attendees' => count($externalAttendees),
                'action_items' => $meeting->actionItems->count(),
                'open_action_items' => $meeting->actionItems->where('status', '!=', 'done')->count(),
            ],
        ];
    }

    protected function participantSummary(array $internalAttendees, array $externalAttendees): string
    {
        return collect([
            count($internalAttendees) ? count($internalAttendees) . ' internal' : null,
            count($externalAttendees) ? count($externalAttendees) . ' external' : null,
        ])->filter()->implode(' / ') ?: 'No participants';
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function taskStatusLabel(string $status): string
    {
        return match ($status) {
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
