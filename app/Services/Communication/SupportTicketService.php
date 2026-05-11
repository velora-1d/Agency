<?php

namespace App\Services\Communication;

use App\Events\SupportTicketCreated;
use App\Models\ActivityFeed;
use App\Models\SupportTicket;
use App\Models\Workspace;
use App\Repositories\Communication\SupportTicketRepository;
use Illuminate\Support\Facades\Auth;

class SupportTicketService
{
    public function __construct(
        protected SupportTicketRepository $repository
    ) {}

    public function create(Workspace $workspace, array $data): SupportTicket
    {
        $priority = $data['priority'] ?? 'medium';
        $slaDueAt = $this->calculateSla($priority);

        $ticketData = array_merge($data, [
            'workspace_id' => $workspace->id,
            'sla_due_at' => $slaDueAt,
            'status' => 'open',
        ]);

        $ticket = $this->repository->create($ticketData);

        event(new SupportTicketCreated($ticket));

        $this->logActivity($workspace, $ticket, "Support Ticket #{$ticket->id} created: {$ticket->title}", 'create', 'emerald');

        return $ticket;
    }

    public function update(Workspace $workspace, SupportTicket $ticket, array $data): SupportTicket
    {
        abort_unless($ticket->workspace_id === $workspace->id, 404);

        if (isset($data['priority']) && $data['priority'] !== $ticket->priority && $ticket->status === 'open') {
            $data['sla_due_at'] = $this->calculateSla($data['priority']);
        }

        if (isset($data['status']) && $data['status'] !== $ticket->status) {
            if ($data['status'] === 'resolved' || $data['status'] === 'closed') {
                $data['resolved_at'] = now();
            } else {
                $data['resolved_at'] = null;
            }
        }

        $ticket = $this->repository->update($ticket, $data);

        $this->logActivity($workspace, $ticket, "Support Ticket #{$ticket->id} updated.", 'update', 'amber');

        return $ticket;
    }

    public function delete(Workspace $workspace, SupportTicket $ticket): void
    {
        abort_unless($ticket->workspace_id === $workspace->id, 404);

        $ticketId = $ticket->id;
        $this->repository->delete($ticket);

        $this->logActivity($workspace, null, "Support Ticket #{$ticketId} deleted.", 'delete', 'rose');
    }

    protected function calculateSla(string $priority): \DateTime
    {
        $hours = match ($priority) {
            'urgent' => 2,
            'high' => 8,
            'medium' => 24,
            'low' => 48,
            default => 24,
        };

        return now()->addHours($hours);
    }

    protected function logActivity(
        Workspace $workspace,
        ?SupportTicket $ticket,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => Auth::id(),
            'type' => 'support_ticket',
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket?->id,
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'TicketPlus',
                    'update' => 'Pencil',
                    'delete' => 'Trash2',
                    default => 'Ticket',
                },
                'color' => $color,
            ],
        ]);
    }
}
