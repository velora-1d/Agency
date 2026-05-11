<?php

namespace App\Repositories\Communication;

use App\Models\SupportTicket;

class SupportTicketRepository
{
    public function create(array $attributes): SupportTicket
    {
        return SupportTicket::query()->create($attributes);
    }

    public function update(SupportTicket $ticket, array $attributes): SupportTicket
    {
        $ticket->fill($attributes);
        $ticket->save();

        return $ticket->refresh();
    }

    public function delete(SupportTicket $ticket): void
    {
        $ticket->delete();
    }
}
