<?php

namespace App\Services\Communication;

use App\Models\CalendarEvent;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class CalendarService
{
    public function createEvent(Workspace $workspace, array $data): CalendarEvent
    {
        return CalendarEvent::create([
            'workspace_id' => $workspace->id,
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => 'event',
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'] ?? null,
            'all_day' => $data['all_day'] ?? false,
            'color' => $data['color'] ?? 'blue',
        ]);
    }

    public function deleteEvent(Workspace $workspace, CalendarEvent $event): bool
    {
        if ($event->workspace_id !== $workspace->id) {
            return false;
        }

        return $event->delete();
    }
}
