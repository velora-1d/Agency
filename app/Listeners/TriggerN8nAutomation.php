<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Events\LeadCreated;
use App\Services\Automation\N8nAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class TriggerN8nAutomation implements ShouldQueue
{
    public function __construct(protected N8nAutomationService $service)
    {}

    public function handle(object $event): void
    {
        $record = $event->model ?? $event->record ?? null; // Laravel event might pass as model
        
        // Handle Eloquent event structure
        if (!$record && isset($event->invoice)) $record = $event->invoice;
        if (!$record && isset($event->lead)) $record = $event->lead;
        if (!$record && isset($event->ticket)) $record = $event->ticket;

        if (!$record) return;

        // Specific logic for InvoicePaid: only trigger if status just changed to paid
        if ($event instanceof InvoicePaid && $record->status !== 'paid') {
            return;
        }

        $eventName = str(get_class($event))->afterLast('\\')->snake()->toString();
        $workspaceId = $record->workspace_id ?? null;

        if ($workspaceId) {
            $this->service->trigger($workspaceId, $eventName, $record->toArray());
        }
    }
}
