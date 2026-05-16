<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user {email : Alamat email pengguna} {--password= : Kata sandi pengguna} {--name= : Nama lengkap pengguna} {--workspace= : Slug workspace (opsional, all untuk semua)} {--role=owner : Slug role di workspace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat akun pengguna baru dan mengaitkannya ke workspace secara aman via CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Masukkan kata sandi untuk ' . $email);
        $name = $this->option('name') ?: $this->ask('Masukkan nama lengkap', 'Administrator');
        $workspaceSlug = $this->option('workspace');
        $roleSlug = $this->option('role');

        if (empty($password)) {
            $this->error('Kata sandi tidak boleh kosong.');
            return self::FAILURE;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info("Akun {$email} berhasil dipastikan/dibuat.");

        if ($workspaceSlug === 'all') {
            Workspace::query()->each(function (Workspace $workspace) use ($user, $roleSlug): void {
                $role = Role::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('slug', $roleSlug)
                    ->first();

                $workspace->users()->syncWithoutDetaching([
                    $user->getKey() => [
                        'id' => (string) Str::uuid(),
                        'role_id' => $role?->getKey(),
                        'is_owner' => $roleSlug === 'owner',
                        'joined_at' => now(),
                        'expires_at' => null,
                    ],
                ]);
            });
            $this->info("Pengguna {$email} berhasil ditambahkan ke semua workspace dengan role '{$roleSlug}'.");
        } elseif ($workspaceSlug) {
            $workspace = Workspace::query()->where('slug', $workspaceSlug)->first();
            if (! $workspace) {
                $this->error("Workspace dengan slug '{$workspaceSlug}' tidak ditemukan.");
                return self::FAILURE;
            }

            $role = Role::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('slug', $roleSlug)
                ->first();

            $workspace->users()->syncWithoutDetaching([
                $user->getKey() => [
                    'id' => (string) Str::uuid(),
                    'role_id' => $role?->getKey(),
                    'is_owner' => $roleSlug === 'owner',
                    'joined_at' => now(),
                    'expires_at' => null,
                ],
            ]);
            $this->info("Pengguna {$email} berhasil ditambahkan ke workspace '{$workspaceSlug}' dengan role '{$roleSlug}'.");
        }

        return self::SUCCESS;
    }
}
