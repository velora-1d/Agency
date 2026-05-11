<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SupportTicketPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_ticket_index_page_renders_without_server_error(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.communication.support-tickets.index', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Communication/SupportTickets/Index')
                ->where('workspace.slug', 'velora')
                ->has('tickets.data')
                ->has('clients')
                ->has('team')
                ->where('filters.search', '')
                ->where('filters.status', '')
                ->where('filters.priority', '')
            );
    }
}
