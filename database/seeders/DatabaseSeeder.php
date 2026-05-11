<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WorkspaceSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            PipelineSeeder::class,
        ]);

        if ($this->command?->getOutput() !== null && (app()->isLocal() || app()->environment('testing'))) {
            $this->call(LocalAdminSeeder::class);
            $this->call(LocalClientSeeder::class);
        }
    }
}
