<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LocalClientSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $workspace = Workspace::query()->where('slug', 'velora')->first();

        if (! $workspace) {
            return;
        }

        $clientRole = Role::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('slug', 'client')
            ->first();

        $user = User::query()->firstOrCreate(
            ['email' => env('DEV_CLIENT_EMAIL', 'client@kantordigital.test')],
            [
                'name' => env('DEV_CLIENT_NAME', 'Velora Client'),
                'password' => Hash::make(env('DEV_CLIENT_PASSWORD', 'password')),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $workspace->users()->syncWithoutDetaching([
            $user->getKey() => [
                'id' => (string) Str::uuid(),
                'role_id' => $clientRole?->getKey(),
                'is_owner' => false,
                'joined_at' => now(),
                'expires_at' => null,
            ],
        ]);
    }
}
