<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_returns_custom_inertia_workspace_dashboard(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.dashboard', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Dashboard/Index')
                ->where('workspace.slug', 'velora')
                ->where('navigation.0.section', 'Main')
                ->where('navigation.0.items.0.label', 'Dashboard')
                ->where('dashboard.context.workspace.slug', 'velora')
                ->has('dashboard.metrics', 8)
                ->has('dashboard.charts.revenue.filters')
                ->has('dashboard.charts.leadsConversion.filters')
                ->has('dashboard.charts.projectProgress.labels')
                ->has('dashboard.charts.monthlyGrowth.labels')
                ->has('dashboard.quickActions', 5)
                ->has('dashboard.categorySummary', 4)
            );
    }

    public function test_app_home_redirects_to_first_accessible_workspace_dashboard(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        $response = $this
            ->actingAs($owner)
            ->get(route('app.home'));

        $response->assertRedirectToRoute('workspace.dashboard', $owner->firstAccessibleWorkspace());
    }

    public function test_project_manager_only_receives_project_focused_dashboard_payload(): void
    {
        $this->seed();

        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $role = Role::withoutGlobalScopes()->firstOrCreate(
            [
                'workspace_id' => $workspace->getKey(),
                'slug' => 'project-manager',
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Handles project delivery and team execution.',
                'is_default' => true,
            ],
        );
        $user = User::factory()->create();

        $workspace->users()->syncWithoutDetaching([
            $user->getKey() => [
                'id' => (string) Str::uuid(),
                'role_id' => $role->getKey(),
                'is_owner' => false,
                'joined_at' => now(),
                'expires_at' => null,
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('workspace.dashboard', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Dashboard/Index')
                ->where('dashboard.context.role', 'project-manager')
                ->where('dashboard.context.roleLabel', 'Project Manager')
                ->where('dashboard.visibility.metrics', ['projects', 'tasks', 'productivity'])
                ->where('dashboard.visibility.charts', ['projectProgress'])
                ->where('dashboard.visibility.categories', ['project'])
                ->where('dashboard.visibility.alerts', ['overdue-tasks', 'incoming-meetings'])
                ->has('dashboard.metrics', 3)
                ->missing('dashboard.charts.revenue')
                ->missing('dashboard.charts.leadsConversion')
                ->has('dashboard.charts.projectProgress')
                ->missing('dashboard.charts.monthlyGrowth')
                ->has('dashboard.categorySummary', 1)
            );
    }
}
