<?php

namespace App\Modules\Communication\Calendar\Queries;

use App\Models\CalendarEvent;
use App\Models\Invoice;
use App\Models\MarketingCampaign;
use App\Models\Meeting;
use App\Models\SocialPost;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Collection;

class CalendarQuery
{
    public function getEvents(Workspace $workspace, string $start, string $end): Collection
    {
        $events = collect();

        // 1. Manual Calendar Events
        $events = $events->concat(
            CalendarEvent::where('workspace_id', $workspace->id)
                ->whereBetween('start_at', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'start' => $item->start_at->toIso8601String(),
                    'end' => $item->end_at?->toIso8601String(),
                    'type' => 'event',
                    'all_day' => (bool) $item->all_day,
                    'color' => $item->color ?? 'blue',
                    'raw' => $item,
                ])
        );

        // 2. Meetings
        $events = $events->concat(
            Meeting::where('workspace_id', $workspace->id)
                ->whereBetween('scheduled_at', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => "[Meeting] " . $item->title,
                    'description' => $item->description,
                    'start' => $item->scheduled_at->toIso8601String(),
                    'end' => $item->scheduled_at->addMinutes($item->duration_minutes ?? 60)->toIso8601String(),
                    'type' => 'meeting',
                    'all_day' => false,
                    'color' => 'purple',
                    'raw' => $item,
                ])
        );

        // 3. Tasks
        $events = $events->concat(
            Task::where('workspace_id', $workspace->id)
                ->whereBetween('due_date', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => "[Task] " . $item->title,
                    'description' => $item->description,
                    'start' => $item->due_date->toIso8601String(),
                    'end' => $item->due_date->toIso8601String(),
                    'type' => 'task',
                    'all_day' => true,
                    'color' => 'emerald',
                    'raw' => $item,
                ])
        );

        // 4. Invoices
        $events = $events->concat(
            Invoice::where('workspace_id', $workspace->id)
                ->whereBetween('due_date', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => "[Invoice] #" . $item->number,
                    'description' => "Due date for invoice " . $item->number,
                    'start' => $item->due_date->toIso8601String(),
                    'end' => $item->due_date->toIso8601String(),
                    'type' => 'invoice',
                    'all_day' => true,
                    'color' => 'amber',
                    'raw' => $item,
                ])
        );

        // 5. Social Posts
        $events = $events->concat(
            SocialPost::where('workspace_id', $workspace->id)
                ->whereBetween('scheduled_at', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => "[Social] " . ($item->caption ? substr($item->caption, 0, 30) . '...' : 'Post'),
                    'description' => $item->caption,
                    'start' => $item->scheduled_at->toIso8601String(),
                    'end' => $item->scheduled_at->toIso8601String(),
                    'type' => 'social',
                    'all_day' => false,
                    'color' => 'sky',
                    'raw' => $item,
                ])
        );

        // 6. Marketing Campaigns
        $events = $events->concat(
            MarketingCampaign::where('workspace_id', $workspace->id)
                ->whereBetween('start_date', [$start, $end])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => "[Campaign] " . $item->name,
                    'description' => "Campaign start date",
                    'start' => $item->start_date->toIso8601String(),
                    'end' => $item->end_date?->toIso8601String() ?? $item->start_date->toIso8601String(),
                    'type' => 'campaign',
                    'all_day' => true,
                    'color' => 'rose',
                    'raw' => $item,
                ])
        );

        return $events;
    }
}
