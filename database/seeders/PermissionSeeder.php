<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $modules = [
            'dashboard',
            'workspaces',
            'users',
            'roles',
            'permissions',
            'pipelines',
            'leads',
            'clients',
            'contracts',
            'support_tickets',
            'activity_feed',
            'calendar_events',
            'projects',
            'tasks',
            'finance',
            'automation',
            'marketing',
            'settings',
            'audit_logs',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::query()->updateOrCreate(
                    [
                        'module' => $module,
                        'action' => $action,
                    ],
                    [
                        'description' => ucfirst($action) . " {$module}",
                    ],
                );
            }
        }
    }
}
