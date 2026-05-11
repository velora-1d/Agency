<?php

namespace App\Services;

use App\Models\Workspace;

class NavigationService
{
    public function getNavigation(Workspace $workspace, string $activeLabel = ''): array
    {
        return [
            [
                'section' => 'Main',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'active' => $activeLabel === 'Dashboard',
                        'href' => route('workspace.dashboard', $workspace),
                    ],
                    [
                        'label' => 'Activity Feed',
                        'active' => $activeLabel === 'Activity Feed',
                        'href' => route('workspace.communication.activity-feed', $workspace),
                    ],
                    [
                        'label' => 'Calendar',
                        'active' => $activeLabel === 'Calendar',
                        'href' => route('workspace.communication.calendar', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'CRM & Client',
                'items' => [
                    [
                        'label' => 'Leads / CRM',
                        'active' => $activeLabel === 'Leads',
                        'href' => route('workspace.crm.leads.index', $workspace),
                    ],
                    [
                        'label' => 'Clients',
                        'active' => $activeLabel === 'Clients',
                        'href' => route('workspace.crm.clients.index', $workspace),
                    ],
                    [
                        'label' => 'Contracts',
                        'active' => $activeLabel === 'Contracts',
                        'href' => route('workspace.projects.contracts.index', $workspace),
                    ],
                    [
                        'label' => 'Support Tickets',
                        'active' => $activeLabel === 'Support Tickets',
                        'href' => route('workspace.communication.support-tickets.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Project Management',
                'items' => [
                    [
                        'label' => 'Projects',
                        'active' => $activeLabel === 'Projects',
                        'href' => route('workspace.projects.index', $workspace),
                    ],
                    [
                        'label' => 'Tasks',
                        'active' => $activeLabel === 'Tasks',
                        'href' => route('workspace.tasks.index', $workspace),
                    ],
                    [
                        'label' => 'Meetings',
                        'active' => $activeLabel === 'Meetings',
                        'href' => route('workspace.meetings.index', $workspace),
                    ],
                    [
                        'label' => 'Notes / SOP',
                        'active' => $activeLabel === 'Notes',
                        'href' => route('workspace.notes.index', $workspace),
                    ],
                    [
                        'label' => 'Files',
                        'active' => $activeLabel === 'Files',
                        'href' => route('workspace.files.index', $workspace),
                    ],
                ],
            ],
            [
                'section' => 'Finance',
                'items' => [
                    [
                        'label' => 'Finance Overview',
                        'active' => $activeLabel === 'Finance Overview',
                        'href' => route('workspace.finance.overview', $workspace),
                    ],
                    [
                        'label' => 'Quotation',
                        'active' => $activeLabel === 'Quotation',
                        'href' => '#', // To be implemented
                    ],
                    [
                        'label' => 'Invoices',
                        'active' => $activeLabel === 'Invoices',
                        'href' => '#', // To be implemented
                    ],
                    [
                        'label' => 'Transactions',
                        'active' => $activeLabel === 'Transactions',
                        'href' => '#', // To be implemented
                    ],
                ],
            ],
            [
                'section' => 'Communication',
                'items' => [
                    [
                        'label' => 'Inbox',
                        'active' => $activeLabel === 'Inbox',
                        'href' => route('workspace.communication.inbox', $workspace),
                    ],
                    [
                        'label' => 'Notifications',
                        'active' => $activeLabel === 'Notifications',
                        'href' => '#', // To be implemented
                    ],
                ],
            ],
        ];
    }
}
