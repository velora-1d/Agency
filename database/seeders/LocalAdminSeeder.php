<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => env('DEV_ADMIN_EMAIL', 'owner@kantordigital.test')],
            [
                'name' => env('DEV_ADMIN_NAME', 'Kantor Digital Owner'),
                'password' => Hash::make(env('DEV_ADMIN_PASSWORD', 'password')),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        Workspace::query()->each(function (Workspace $workspace) use ($user): void {
            $ownerRole = Role::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('slug', 'owner')
                ->first();

            $workspace->users()->syncWithoutDetaching([
                $user->getKey() => [
                    'id' => (string) str()->uuid(),
                    'role_id' => $ownerRole?->getKey(),
                    'is_owner' => true,
                    'joined_at' => now(),
                    'expires_at' => null,
                ],
            ]);
        });
    }
}
