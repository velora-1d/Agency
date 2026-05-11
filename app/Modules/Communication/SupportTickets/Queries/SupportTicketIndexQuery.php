<?php

namespace App\Modules\Communication\SupportTickets\Queries;

use App\Models\SupportTicket;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupportTicketIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $filters = []): array
    {
        $tickets = SupportTicket::query()
            ->where('workspace_id', $workspace->id)
            ->with(['client:id,company_name', 'assignee:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['priority'] ?? null, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return [
            'tickets' => $tickets,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
            ],
        ];
    }
}
