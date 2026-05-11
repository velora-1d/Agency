<?php

namespace Database\Seeders;

use App\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $workspaces = [
            [
                'name' => 'Velora',
                'slug' => 'velora',
                'primary_color' => '#F59E0B',
            ],
            [
                'name' => 'Maven',
                'slug' => 'maven',
                'primary_color' => '#0F766E',
            ],
        ];

        foreach ($workspaces as $workspace) {
            Workspace::query()->updateOrCreate(
                ['slug' => $workspace['slug']],
                [
                    ...$workspace,
                    'timezone' => 'Asia/Jakarta',
                    'currency' => 'IDR',
                    'language' => 'id',
                    'storage_quota_gb' => 50,
                    'settings' => [
                        'notifications' => [
                            'email' => true,
                            'whatsapp' => false,
                        ],
                    ],
                ],
            );
        }
    }
}
