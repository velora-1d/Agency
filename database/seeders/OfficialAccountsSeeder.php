<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficialAccountsSeeder extends Seeder
{
    /**
     * Seed the application's official accounts from environment variables.
     */
    public function run(): void
    {
        $veloraWorkspace = Workspace::query()->where('slug', 'velora')->first();
        $mavenWorkspace = Workspace::query()->where('slug', 'maven')->first();

        // 1. Owner Account (Accesses all workspaces)
        $ownerEmail = env('APP_OWNER_EMAIL');
        $ownerPassword = env('APP_OWNER_PASSWORD');

        if ($ownerEmail && $ownerPassword) {
            $owner = User::query()->firstOrCreate(
                ['email' => $ownerEmail],
                [
                    'name' => env('APP_OWNER_NAME', 'Mahin Utsman Nawawi'),
                    'password' => Hash::make($ownerPassword),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            Workspace::query()->each(function (Workspace $workspace) use ($owner): void {
                $ownerRole = Role::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('slug', 'owner')
                    ->first();

                $workspace->users()->syncWithoutDetaching([
                    $owner->getKey() => [
                        'id' => (string) Str::uuid(),
                        'role_id' => $ownerRole?->getKey(),
                        'is_owner' => true,
                        'joined_at' => now(),
                        'expires_at' => null,
                    ],
                ]);
            });
        }

        // 2. Velora Account
        $veloraEmail = env('APP_VELORA_EMAIL');
        $veloraPassword = env('APP_VELORA_PASSWORD');

        if ($veloraWorkspace && $veloraEmail && $veloraPassword) {
            $veloraUser = User::query()->firstOrCreate(
                ['email' => $veloraEmail],
                [
                    'name' => env('APP_VELORA_NAME', 'Velora Admin'),
                    'password' => Hash::make($veloraPassword),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $veloraRole = Role::query()
                ->where('workspace_id', $veloraWorkspace->getKey())
                ->where('slug', 'owner')
                ->first();

            $veloraWorkspace->users()->syncWithoutDetaching([
                $veloraUser->getKey() => [
                    'id' => (string) Str::uuid(),
                    'role_id' => $veloraRole?->getKey(),
                    'is_owner' => true,
                    'joined_at' => now(),
                    'expires_at' => null,
                ],
            ]);
        }

        // 3. Maven Account
        $mavenEmail = env('APP_MAVEN_EMAIL');
        $mavenPassword = env('APP_MAVEN_PASSWORD');

        if ($mavenWorkspace && $mavenEmail && $mavenPassword) {
            $mavenUser = User::query()->firstOrCreate(
                ['email' => $mavenEmail],
                [
                    'name' => env('APP_MAVEN_NAME', 'Maven Admin'),
                    'password' => Hash::make($mavenPassword),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $mavenRole = Role::query()
                ->where('workspace_id', $mavenWorkspace->getKey())
                ->where('slug', 'owner')
                ->first();

            $mavenWorkspace->users()->syncWithoutDetaching([
                $mavenUser->getKey() => [
                    'id' => (string) Str::uuid(),
                    'role_id' => $mavenRole?->getKey(),
                    'is_owner' => true,
                    'joined_at' => now(),
                    'expires_at' => null,
                ],
            ]);
        }
    }
}

