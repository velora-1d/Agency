<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_fixed_workspaces_and_local_owner(): void
    {
        $this->seed();

        $this->assertSame(2, Workspace::query()->count());
        $this->assertDatabaseHas('workspaces', ['slug' => 'velora']);
        $this->assertDatabaseHas('workspaces', ['slug' => 'maven']);

        $owner = User::query()->where('email', 'owner@kantordigital.test')->first();

        $this->assertNotNull($owner);
        $this->assertTrue($owner->is_active);
        $this->assertCount(2, $owner->workspaces);
    }

    public function test_seeded_users_are_partitioned_between_workspaces_and_access_scope(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();
        $client = User::query()->where('email', 'client@kantordigital.test')->firstOrFail();
        $velora = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $maven = Workspace::query()->where('slug', 'maven')->firstOrFail();

        $this->assertNotNull($owner->firstAccessibleWorkspace());
        $this->assertTrue($owner->canAccessTenant($velora));
        $this->assertTrue($owner->canAccessTenant($maven));
        $this->assertTrue($client->canAccessTenant($velora));
        $this->assertFalse($client->canAccessTenant($maven));
    }

    public function test_updating_auditable_model_creates_audit_log(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();

        $this->actingAs($owner);

        $workspace->update([
            'name' => 'Velora Updated',
        ]);

        $this->assertDatabaseHas(AuditLog::class, [
            'workspace_id' => $workspace->getKey(),
            'user_id' => $owner->getKey(),
            'module' => 'workspaces',
            'action' => 'update',
        ]);
    }
}
