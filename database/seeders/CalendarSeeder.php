<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\MarketingCampaign;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\SocialPost;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::first();
        if (!$workspace) return;

        $user = User::first();
        $client = Client::first() ?? Client::create([
            'workspace_id' => $workspace->id,
            'company_name' => 'Acme Corp',
            'email' => 'contact@acme.com',
            'status' => 'active',
        ]);

        $project = Project::first() ?? Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Sample Agency Project',
            'status' => 'active',
            'start_date' => now(),
            'created_by' => $user->id,
        ]);

        // 1. Meetings
        Meeting::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'client_id' => $client->id,
            'title' => 'Weekly Sync Agency',
            'description' => 'Review progress for all active projects.',
            'scheduled_at' => now()->startOfWeek()->addHours(10),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        Meeting::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'client_id' => $client->id,
            'title' => 'Client Pitch: Velora AI',
            'description' => 'Presenting the new AI strategy.',
            'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0),
            'duration_minutes' => 90,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        // 2. Tasks
        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'title' => 'Finalize Landing Page Design',
            'description' => 'Finish all high-fidelity mockups.',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(1),
            'assigned_to' => $user->id,
            'created_by' => $user->id,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'title' => 'API Integration - Payment Gateway',
            'description' => 'Connect Stripe to the backend.',
            'status' => 'in_progress',
            'priority' => 'critical',
            'due_date' => now()->addDays(4),
            'assigned_to' => $user->id,
            'created_by' => $user->id,
        ]);

        // 3. Invoices
        Invoice::updateOrCreate(
            ['number' => 'INV-2026-001'],
            [
                'workspace_id' => $workspace->id,
                'client_id' => $client->id,
                'project_id' => $project->id,
                'status' => 'sent',
                'subtotal' => 5000000,
                'total' => 5000000,
                'due_date' => now()->addDays(7),
                'created_by' => $user->id,
            ]
        );

        // 4. Social Posts
        SocialPost::create([
            'workspace_id' => $workspace->id,
            'caption' => 'Launching our new website today! #AgencyLife',
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(5),
            'created_by' => $user->id,
        ]);

        // 5. Campaigns
        MarketingCampaign::create([
            'workspace_id' => $workspace->id,
            'name' => 'Summer Sale 2026',
            'type' => 'email',
            'status' => 'active',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        // 6. Manual Events
        CalendarEvent::create([
            'workspace_id' => $workspace->id,
            'title' => 'Office Renovation',
            'description' => 'The main office will be closed for renovation.',
            'type' => 'holiday',
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(12),
            'all_day' => true,
            'color' => 'amber',
            'created_by' => $user->id,
        ]);
    }
}
