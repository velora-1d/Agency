<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissionIds = Permission::query()
            ->pluck('id')
            ->all();

        $roleDefinitions = [
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Full access to the workspace.',
                'is_default' => false,
                'permissions' => $permissionIds,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Operational admin access across workspace modules.',
                'is_default' => false,
                'permissions' => $permissionIds,
            ],
            [
                'name' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'Handles project delivery and team execution.',
                'is_default' => true,
                'permissions' => Permission::query()
                    ->whereIn('module', ['dashboard', 'clients', 'projects', 'tasks'])
                    ->pluck('id')
                    ->all(),
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Handles leads and marketing activity.',
                'is_default' => true,
                'permissions' => Permission::query()
                    ->whereIn('module', ['dashboard', 'leads', 'clients', 'marketing'])
                    ->pluck('id')
                    ->all(),
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Handles finance and billing workflows.',
                'is_default' => true,
                'permissions' => Permission::query()
                    ->whereIn('module', ['dashboard', 'clients', 'finance'])
                    ->pluck('id')
                    ->all(),
            ],
            [
                'name' => 'Client',
                'slug' => 'client',
                'description' => 'Read-only workspace access for client portal users.',
                'is_default' => false,
                'permissions' => Permission::query()
                    ->where('module', 'dashboard')
                    ->where('action', 'view')
                    ->pluck('id')
                    ->all(),
            ],
        ];

        Workspace::query()->each(function (Workspace $workspace) use ($roleDefinitions): void {
            foreach ($roleDefinitions as $definition) {
                $role = Role::query()->updateOrCreate(
                    [
                        'workspace_id' => $workspace->getKey(),
                        'slug' => $definition['slug'],
                    ],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'is_default' => $definition['is_default'],
                    ],
                );

                $role->permissions()->sync($definition['permissions']);
            }
        });
    }
}
