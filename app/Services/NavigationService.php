<?php

namespace App\Services;

use App\Models\Workspace;

class NavigationService
{
    public function getNavigation(\App\Models\Workspace $workspace, string $activeLabel = '', ?\App\Models\User $user = null): array
    {
        $nav = [
            [
                'section' => 'Utama',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'nav_label' => 'Dashboard',
                        'description' => 'Ringkasan workspace',
                        'icon' => 'dashboard',
                        'active' => $activeLabel === 'Dashboard',
                        'href' => route('workspace.dashboard', $workspace),
                    ],
                ],
            ],
        ];

        // Tambahkan Pusat Kendali khusus Owner
        if ($user) {
            $membership = $user->workspaceMemberships()->where('workspace_id', $workspace->id)->first();
            if ($membership?->is_owner) {
                $nav[0]['items'][] = [
                    'label' => 'Pusat Kendali',
                    'nav_label' => 'Pusat Kendali',
                    'description' => 'Agregasi data lintas brand (Velora & Maven)',
                    'icon' => 'dashboard', // We can use a different icon
                    'active' => $activeLabel === 'Executive Hub',
                    'href' => route('workspace.system.executive-hub', $workspace),
                ];
            }
        }

        $nav[0]['items'][] = [
            'label' => 'Alur Aktivitas',
            'nav_label' => 'Aktivitas',
            'description' => 'Jejak aktivitas workspace',
            'icon' => 'activity',
            'active' => $activeLabel === 'Activity Feed',
            'href' => route('workspace.communication.activity-feed', $workspace),
        ];

        $nav[0]['items'][] = [
            'label' => 'Kalender',
            'nav_label' => 'Kalender',
            'description' => 'Agenda dan jadwal workspace',
            'icon' => 'calendar',
            'active' => $activeLabel === 'Calendar',
            'href' => route('workspace.communication.calendar', $workspace),
        ];

        return array_merge($nav, [
            [
                'section' => 'CRM & Klien',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Prospek / CRM',
                        'nav_label' => 'Prospek',
                        'description' => 'Pipeline dan sumber prospek',
                        'icon' => 'leads',
                        'active' => $activeLabel === 'Leads',
                        'href' => route('workspace.crm.leads.index', $workspace),
                    ],
                    [
                        'label' => 'Klien',
                        'nav_label' => 'Klien',
                        'description' => 'Data akun klien',
                        'icon' => 'clients',
                        'active' => $activeLabel === 'Clients',
                        'href' => route('workspace.crm.clients.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Manajemen Kerja',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Operasional',
                        'nav_label' => 'Operasional',
                        'description' => 'Pusat eksekusi proyek, tugas, rapat, SOP, dan berkas kerja',
                        'icon' => 'projects',
                        'active' => $activeLabel === 'Projects',
                        'href' => route('workspace.projects.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Keuangan',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Keuangan',
                        'nav_label' => 'Keuangan',
                        'description' => 'Ringkasan dan buku besar',
                        'icon' => 'finance',
                        'active' => in_array($activeLabel, [
                            'Finance', 
                            'Finance Overview', 
                            'Quotation', 
                            'Invoices', 
                            'Transactions', 
                            'Subscriptions', 
                            'Billing', 
                            'Payroll & Fee', 
                            'Kas & Bank', 
                            'Expense & Reimbursement', 
                            'Laporan Keuangan'
                        ]),
                        'href' => route('workspace.finance.overview', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Komunikasi',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Komunikasi',
                        'nav_label' => 'Komunikasi',
                        'description' => 'Kotak masuk, tiket dukungan terintegrasi n8n, dan notifikasi otomatis WhatsApp',
                        'icon' => 'inbox',
                        'active' => in_array($activeLabel, [
                            'Inbox',
                            'Support Tickets',
                            'Notifications',
                            'Communication',
                        ], true),
                        'href' => route('workspace.communication.inbox', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Otomasi',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Otomasi',
                        'nav_label' => 'Otomasi',
                        'description' => 'Alur kerja otomatis',
                        'icon' => 'automation',
                        'active' => in_array($activeLabel, [
                            'Automation',
                            'AI Tools',
                            'Integrations',
                            'API Keys',
                        ], true),
                        'href' => route('workspace.automation.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Infrastruktur Digital',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Aset Digital',
                        'nav_label' => 'Infrastruktur',
                        'description' => 'Manajemen situs web, domain, server/VPS, dan formulir otomatis',
                        'icon' => 'websites',
                        'active' => in_array($activeLabel, [
                            'Digital Services',
                            'Website Manager',
                            'Deployments',
                            'Domains',
                            'Hosting / VPS',
                            'Forms',
                        ], true),
                        'href' => route('workspace.digital-services.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Pemasaran',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Pemasaran',
                        'nav_label' => 'Pemasaran',
                        'description' => 'Kampanye, perencana sosial, email, dan analitik',
                        'icon' => 'marketing',
                        'active' => in_array($activeLabel, [
                            'Marketing',
                            'Marketing Overview',
                            'Social Media Planner',
                            'Email Campaigns',
                            'Analytics',
                        ], true),
                        'href' => route('workspace.marketing.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Sistem',
                'layout' => 'tabs',
                'items' => [
                    [
                        'label' => 'Sistem',
                        'nav_label' => 'Sistem',
                        'description' => 'Peran, pengaturan, audit, keamanan, dan pusat bantuan',
                        'icon' => 'system',
                        'active' => in_array($activeLabel, [
                            'System',
                            'Role & Permissions',
                            'Workspace Settings',
                            'Audit Logs',
                            'Security',
                            'Help Center',
                        ], true),
                        'href' => route('workspace.system.index', $workspace),
                    ],
                ],
            ],
        ]);
    }
}
