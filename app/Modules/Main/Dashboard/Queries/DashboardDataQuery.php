<?php

namespace App\Modules\Main\Dashboard\Queries;

use App\Models\ActivityFeed;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\Meeting;
use App\Models\Message;
use App\Models\PipelineStage;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Dashboard\DashboardFormat;
use App\Support\Dashboard\DashboardRole;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardDataQuery
{
    public function forWorkspace(Workspace $workspace, ?User $user = null, array $filters = []): array
    {
        $user ??= Auth::user();

        $timezone = DashboardFormat::timezone($workspace->timezone ?? null);
        $currency = $workspace->currency ?? 'IDR';
        $role = DashboardRole::current($workspace->getKey(), $user) ?? 'staff';
        $now = now()->timezone($timezone);
        $visibility = $this->resolveVisibility($role);

        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $currentMonthStart->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $currentMonthStart->copy()->subMonth()->endOfMonth();

        $metrics = $this->buildMetrics(
            workspace: $workspace,
            currency: $currency,
            timezone: $timezone,
            currentMonthStart: $currentMonthStart,
            currentMonthEnd: $currentMonthEnd,
            previousMonthStart: $previousMonthStart,
            previousMonthEnd: $previousMonthEnd,
            filters: $filters,
        );

        $charts = [
            'revenue' => $this->buildRevenueChart($workspace, $timezone, $filters),
            'leadsConversion' => $this->buildLeadsConversionChart($workspace, $filters),
            'projectProgress' => $this->buildProjectProgressChart($workspace, $filters),
            'monthlyGrowth' => $this->buildMonthlyGrowthChart($workspace, $currency, $timezone, $filters),
        ];

        $categorySummary = $this->buildCategorySummary(
            $workspace,
            $currency,
            $timezone,
            $currentMonthStart,
            $currentMonthEnd,
            $filters,
        );

        $alerts = $this->buildAlerts($workspace, $currency, $timezone, $now);

        return [
            'context' => [
                'workspace' => [
                    'id' => $workspace->getKey(),
                    'name' => $workspace->name,
                    'slug' => $workspace->slug,
                ],
                'generatedAt' => DashboardFormat::fullDateTime($now, $timezone),
                'monthLabel' => DashboardFormat::monthYear($now, $timezone),
                'timezone' => $timezone,
                'currency' => $currency,
                'role' => $role,
                'roleLabel' => $this->roleLabel($role),
            ],
            'visibility' => $visibility,
            'metrics' => $this->filterItemsByKey($metrics, $visibility['metrics']),
            'charts' => $this->filterChartsByKey($charts, $visibility['charts']),
            'quickActions' => $this->buildQuickActions($workspace),
            'categorySummary' => $this->filterItemsByKey($categorySummary, $visibility['categories']),
            'recentActivity' => $this->buildRecentActivity($workspace, $timezone, $filters),
            'alerts' => $this->filterItemsByKey($alerts, $visibility['alerts']),
            'upcomingMeetings' => $this->buildUpcomingMeetings($workspace, $timezone, $now),
            'calendar' => $this->buildCalendar($workspace, $timezone, $now),
            'recentFiles' => $this->buildRecentFiles($workspace, $timezone),
            'filterOptions' => $this->buildFilterOptions($workspace),
            'activeFilters' => $filters,
        ];
    }

    protected function buildMetrics(
        Workspace $workspace,
        string $currency,
        string $timezone,
        CarbonInterface $currentMonthStart,
        CarbonInterface $currentMonthEnd,
        CarbonInterface $previousMonthStart,
        CarbonInterface $previousMonthEnd,
        array $filters = [],
    ): array {
        $revenueCurrentQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd]);
        $this->applyFilters($revenueCurrentQuery, $filters, 'paid_at');
        $revenueCurrent = (float) $revenueCurrentQuery->sum('total');

        $revenuePreviousQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$previousMonthStart, $previousMonthEnd]);
        $this->applyFilters($revenuePreviousQuery, $filters, 'paid_at');
        $revenuePrevious = (float) $revenuePreviousQuery->sum('total');

        $activeClientsCurrentQuery = Client::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active');
        $this->applyFilters($activeClientsCurrentQuery, $filters);
        $activeClientsCurrent = $activeClientsCurrentQuery->count();

        $activeClientsPreviousQuery = Client::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->where('created_at', '<=', $previousMonthEnd);
        $this->applyFilters($activeClientsPreviousQuery, $filters);
        $activeClientsPrevious = $activeClientsPreviousQuery->count();

        $activeProjectsCurrentQuery = Project::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active');
        $this->applyFilters($activeProjectsCurrentQuery, $filters);
        $activeProjectsCurrent = $activeProjectsCurrentQuery->count();

        $activeProjectsPreviousQuery = Project::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->where('created_at', '<=', $previousMonthEnd);
        $this->applyFilters($activeProjectsPreviousQuery, $filters);
        $activeProjectsPrevious = $activeProjectsPreviousQuery->count();

        $pendingTasksCurrentQuery = Task::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['todo', 'in_progress']);
        $this->applyFilters($pendingTasksCurrentQuery, $filters);
        $pendingTasksCurrent = $pendingTasksCurrentQuery->count();

        $pendingTasksPreviousQuery = Task::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->where('created_at', '<=', $previousMonthEnd);
        $this->applyFilters($pendingTasksPreviousQuery, $filters);
        $pendingTasksPrevious = $pendingTasksPreviousQuery->count();

        $unpaidInvoicesCurrentQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue']);
        $this->applyFilters($unpaidInvoicesCurrentQuery, $filters);
        $unpaidInvoicesCurrent = $unpaidInvoicesCurrentQuery->count();

        $unpaidInvoicesPreviousQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->where('created_at', '<=', $previousMonthEnd);
        $this->applyFilters($unpaidInvoicesPreviousQuery, $filters);
        $unpaidInvoicesPrevious = $unpaidInvoicesPreviousQuery->count();

        $automationRunsCurrentQuery = ActivityFeed::query()
            ->where('workspace_id', $workspace->id)
            ->where('type', 'automation')
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
        $this->applyFilters($automationRunsCurrentQuery, $filters);
        $automationRunsCurrent = $automationRunsCurrentQuery->count();

        $automationRunsPreviousQuery = ActivityFeed::query()
            ->where('workspace_id', $workspace->id)
            ->where('type', 'automation')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd]);
        $this->applyFilters($automationRunsPreviousQuery, $filters);
        $automationRunsPrevious = $automationRunsPreviousQuery->count();

        $currentMonthTasksQuery = Task::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
        $this->applyFilters($currentMonthTasksQuery, $filters);
        $currentMonthTasks = $currentMonthTasksQuery;

        $previousMonthTasksQuery = Task::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd]);
        $this->applyFilters($previousMonthTasksQuery, $filters);
        $previousMonthTasks = $previousMonthTasksQuery;

        $currentDone = (clone $currentMonthTasks)->where('status', 'done')->count();
        $currentTotal = (clone $currentMonthTasks)->count();
        $previousDone = (clone $previousMonthTasks)->where('status', 'done')->count();
        $previousTotal = (clone $previousMonthTasks)->count();

        $productivityCurrent = $currentTotal > 0 ? ($currentDone / $currentTotal) * 100 : 0;
        $productivityPrevious = $previousTotal > 0 ? ($previousDone / $previousTotal) * 100 : 0;

        $leadsCurrentQuery = Lead::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
        $this->applyFilters($leadsCurrentQuery, $filters);
        $leadsCurrent = $leadsCurrentQuery->count();

        $leadsPreviousQuery = Lead::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd]);
        $this->applyFilters($leadsPreviousQuery, $filters);
        $leadsPrevious = $leadsPreviousQuery->count();

        return [
            $this->metricCard(
                key: 'revenue',
                label: 'Pendapatan bulan ini',
                value: DashboardFormat::currency($revenueCurrent, $currency),
                icon: 'banknotes',
                helper: 'Tagihan dibayar pada bulan berjalan',
                current: $revenueCurrent,
                previous: $revenuePrevious,
                highIsGood: true,
            ),
            $this->metricCard(
                key: 'clients',
                label: 'Klien aktif',
                value: number_format($activeClientsCurrent, 0, ',', '.'),
                icon: 'building',
                helper: 'Klien dengan status aktif',
                current: $activeClientsCurrent,
                previous: $activeClientsPrevious,
                highIsGood: true,
            ),
            $this->metricCard(
                key: 'projects',
                label: 'Proyek berjalan',
                value: number_format($activeProjectsCurrent, 0, ',', '.'),
                icon: 'briefcase',
                helper: 'Proyek dengan status aktif',
                current: $activeProjectsCurrent,
                previous: $activeProjectsPrevious,
                highIsGood: true,
            ),
            $this->metricCard(
                key: 'tasks',
                label: 'Tugas tertunda',
                value: number_format($pendingTasksCurrent, 0, ',', '.'),
                icon: 'clock',
                helper: 'Status to-do dan sedang diproses',
                current: $pendingTasksCurrent,
                previous: $pendingTasksPrevious,
                highIsGood: false,
            ),
            $this->metricCard(
                key: 'invoices',
                label: 'Tagihan belum bayar',
                value: number_format($unpaidInvoicesCurrent, 0, ',', '.'),
                icon: 'receipt',
                helper: 'Status draf, terkirim, cicil, dan telat',
                current: $unpaidInvoicesCurrent,
                previous: $unpaidInvoicesPrevious,
                highIsGood: false,
            ),
            $this->metricCard(
                key: 'automation',
                label: 'Eksekusi otomasi',
                value: number_format($automationRunsCurrent, 0, ',', '.'),
                icon: 'spark',
                helper: 'Otomasi berjalan pada bulan ini',
                current: $automationRunsCurrent,
                previous: $automationRunsPrevious,
                highIsGood: true,
            ),
            $this->metricCard(
                key: 'productivity',
                label: 'Produktivitas tim',
                value: DashboardFormat::percent($productivityCurrent),
                icon: 'pulse',
                helper: 'Rasio tugas selesai bulan ini',
                current: $productivityCurrent,
                previous: $productivityPrevious,
                highIsGood: true,
            ),
            $this->metricCard(
                key: 'leads',
                label: 'Prospek masuk',
                value: number_format($leadsCurrent, 0, ',', '.'),
                icon: 'funnel',
                helper: 'Prospek baru pada bulan ini',
                current: $leadsCurrent,
                previous: $leadsPrevious,
                highIsGood: true,
            ),
        ];
    }

    protected function buildRevenueChart(Workspace $workspace, string $timezone, array $filters): array
    {
        return [
            'default' => '30d',
            'filters' => [
                '7d' => '7 Hari',
                '30d' => '30 Hari',
                '3m' => '3 Bulan',
                '1y' => '1 Tahun',
            ],
            'series' => [
                '7d' => $this->revenueSeries($workspace, $timezone, '7d', $filters),
                '30d' => $this->revenueSeries($workspace, $timezone, '30d', $filters),
                '3m' => $this->revenueSeries($workspace, $timezone, '3m', $filters),
                '1y' => $this->revenueSeries($workspace, $timezone, '1y', $filters),
            ],
        ];
    }

    protected function buildLeadsConversionChart(Workspace $workspace, array $filters): array
    {
        return [
            'default' => '30d',
            'filters' => [
                '7d' => '7 Hari',
                '30d' => '30 Hari',
                '3m' => '3 Bulan',
            ],
            'series' => [
                '7d' => $this->leadStageSeries($workspace, now()->subDays(7), $filters),
                '30d' => $this->leadStageSeries($workspace, now()->subDays(30), $filters),
                '3m' => $this->leadStageSeries($workspace, now()->subMonths(3), $filters),
            ],
        ];
    }

    protected function buildProjectProgressChart(Workspace $workspace, array $filters): array
    {
        $query = Project::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active');

        $this->applyFilters($query, $filters);

        $projects = $query->orderByDesc('progress')
            ->limit(5)
            ->get(['id', 'name', 'progress']);

        return [
            'labels' => $projects->pluck('name')->all(),
            'values' => $projects->map(fn (Project $project): int => (int) ($project->progress ?? 0))->all(),
            'colors' => $projects->map(function (Project $project): string {
                $progress = (int) ($project->progress ?? 0);

                return match (true) {
                    $progress < 30 => '#d9485f',
                    $progress < 70 => '#d7922d',
                    default => '#1c8c63',
                };
            })->all(),
        ];
    }

    protected function buildMonthlyGrowthChart(Workspace $workspace, string $currency, string $timezone, array $filters): array
    {
        $labels = [];
        $revenue = [];
        $leads = [];
        $projects = [];

        for ($offset = 11; $offset >= 0; $offset--) {
            $date = now()->timezone($timezone)->subMonths($offset);
            $labels[] = DashboardFormat::monthYear($date, $timezone);

            $revenueQuery = Invoice::query()
                ->where('workspace_id', $workspace->id)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year);
            $this->applyFilters($revenueQuery, $filters, 'paid_at');
            $revenue[] = (float) $revenueQuery->sum('total');

            $leadsQuery = Lead::query()
                ->where('workspace_id', $workspace->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);
            $this->applyFilters($leadsQuery, $filters);
            $leads[] = $leadsQuery->count();

            $projectsQuery = Project::query()
                ->where('workspace_id', $workspace->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);
            $this->applyFilters($projectsQuery, $filters);
            $projects[] = $projectsQuery->count();
        }

        return [
            'default' => 'revenue',
            'filters' => [
                'revenue' => 'Pendapatan',
                'leads' => 'Prospek',
                'projects' => 'Proyek',
            ],
            'labels' => $labels,
            'currency' => $currency,
            'series' => [
                'revenue' => $revenue,
                'leads' => $leads,
                'projects' => $projects,
            ],
        ];
    }

    protected function buildQuickActions(Workspace $workspace): array
    {
        $baseUrl = "/w/{$workspace->slug}";

        return [
            [
                'key' => 'add-client',
                'label' => 'Tambah Klien',
                'description' => 'Siapkan onboarding klien baru dari dasbor.',
                'icon' => 'user-plus',
                'href' => "{$baseUrl}/crm/clients",
                'mode' => 'local',
            ],
            [
                'key' => 'create-project',
                'label' => 'Buat Proyek',
                'description' => 'Mulai proyek baru tanpa keluar dari workspace utama.',
                'icon' => 'folder-plus',
                'href' => "{$baseUrl}/projects",
                'mode' => 'local',
            ],
            [
                'key' => 'create-invoice',
                'label' => 'Buat Tagihan',
                'description' => 'Picuan draf tagihan dari area keuangan.',
                'icon' => 'file-plus',
                'href' => "{$baseUrl}/finance",
                'mode' => 'local',
            ],
            [
                'key' => 'trigger-automation',
                'label' => 'Jalankan Otomasi',
                'description' => 'Masuk ke pelari alur kerja untuk tugas manual.',
                'icon' => 'zap',
                'href' => "{$baseUrl}/automation",
                'mode' => 'local',
            ],
            [
                'key' => 'broadcast-wa',
                'label' => 'Siaran WA',
                'description' => 'Siapkan siaran WhatsApp dari ruang komunikasi.',
                'icon' => 'megaphone',
                'href' => "{$baseUrl}/communication/inbox",
                'mode' => 'local',
            ],
        ];
    }

    protected function buildCategorySummary(
        Workspace $workspace,
        string $currency,
        string $timezone,
        CarbonInterface $currentMonthStart,
        CarbonInterface $currentMonthEnd,
        array $filters = [],
    ): array {
        $leadsCountQuery = Lead::query()->where('workspace_id', $workspace->id);
        $this->applyFilters($leadsCountQuery, $filters);
        $leadsCount = $leadsCountQuery->count();

        $hotLeadsQuery = Lead::query()->where('workspace_id', $workspace->id)->where('priority', 'high');
        $this->applyFilters($hotLeadsQuery, $filters);
        $hotLeads = $hotLeadsQuery->count();

        $ongoingProjectsQuery = Project::query()->where('workspace_id', $workspace->id)->where('status', 'active');
        $this->applyFilters($ongoingProjectsQuery, $filters);
        $ongoingProjects = $ongoingProjectsQuery->count();

        $overdueTasksQuery = Task::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', '!=', 'done')
            ->where('due_date', '<', now()->timezone($timezone));
        $this->applyFilters($overdueTasksQuery, $filters);
        $overdueTasks = $overdueTasksQuery->count();

        $incomeQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd]);
        $this->applyFilters($incomeQuery, $filters, 'paid_at');
        $income = (float) $incomeQuery->sum('total');

        $unpaidQuery = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue']);
        $this->applyFilters($unpaidQuery, $filters);
        $unpaid = (float) $unpaidQuery->sum('total');

        $campaignQuery = MarketingCampaign::query()->where('workspace_id', $workspace->id)->where('status', 'active');
        $this->applyFilters($campaignQuery, $filters);
        $campaignCount = $campaignQuery->count();

        $topSpendQuery = MarketingCampaign::query()->where('workspace_id', $workspace->id);
        $this->applyFilters($topSpendQuery, $filters);
        $topSpendName = $topSpendQuery->orderByDesc('spend')->value('name') ?? 'Belum ada campaign';

        return [
            [
                'key' => 'crm',
                'title' => 'CRM',
                'lineOne' => 'Total prospek ' . number_format($leadsCount, 0, ',', '.'),
                'lineTwo' => 'Prospek potensial ' . number_format($hotLeads, 0, ',', '.'),
            ],
            [
                'key' => 'project',
                'title' => 'Operasional',
                'lineOne' => 'Proyek berjalan ' . number_format($ongoingProjects, 0, ',', '.'),
                'lineTwo' => 'Tugas telat ' . number_format($overdueTasks, 0, ',', '.'),
            ],
            [
                'key' => 'finance',
                'title' => 'Keuangan',
                'lineOne' => 'Pendapatan ' . DashboardFormat::currency($income, $currency),
                'lineTwo' => 'Belum bayar ' . DashboardFormat::currency($unpaid, $currency),
            ],
            [
                'key' => 'marketing',
                'title' => 'Pemasaran',
                'lineOne' => 'Kampanye aktif ' . number_format($campaignCount, 0, ',', '.'),
                'lineTwo' => 'Belanja iklan ' . $topSpendName,
            ],
        ];
    }

    protected function buildRecentActivity(Workspace $workspace, string $timezone, array $filters): array
    {
        $query = ActivityFeed::query()
            ->where('workspace_id', $workspace->id);

        $this->applyFilters($query, $filters);

        return $query->with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (ActivityFeed $activity) use ($timezone): array {
                return [
                    'id' => $activity->getKey(),
                    'title' => $this->activityLabel($activity),
                    'description' => $activity->description,
                    'when' => DashboardFormat::humanDiff($activity->created_at, $timezone),
                    'user' => $activity->user?->name ?? 'Sistem',
                    'icon' => $this->activityIcon($activity),
                    'tone' => $this->activityTone($activity),
                ];
            })
            ->all();
    }

    protected function buildAlerts(Workspace $workspace, string $currency, string $timezone, CarbonInterface $now): array
    {
        $overdueTasks = Task::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', '!=', 'done')
            ->where('due_date', '<', $now)
            ->count();

        $overdueInvoices = Invoice::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->where(function ($query) use ($now): void {
                $query->where('status', 'overdue')
                    ->orWhere('due_date', '<', $now->toDateString());
            })
            ->count();

        $unreadClientMessages = Message::query()
            ->where('is_from_client', true)
            ->whereNull('read_at')
            ->whereHas('conversation', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->count();

        $incomingMeetings = Meeting::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('scheduled_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->count();

        return array_values(array_filter([
            $overdueTasks > 0 ? [
                'key' => 'overdue-tasks',
                'label' => 'Tugas terlambat',
                'value' => number_format($overdueTasks, 0, ',', '.') . ' item',
                'description' => 'Butuh follow-up di task board hari ini.',
                'tone' => 'critical',
            ] : null,
            $overdueInvoices > 0 ? [
                'key' => 'overdue-invoices',
                'label' => 'Invoice overdue',
                'value' => number_format($overdueInvoices, 0, ',', '.') . ' invoice',
                'description' => 'Tagihan perlu penagihan atau eskalasi.',
                'tone' => 'warning',
            ] : null,
            $unreadClientMessages > 0 ? [
                'key' => 'unread-client-messages',
                'label' => 'Pesan client belum dibaca',
                'value' => number_format($unreadClientMessages, 0, ',', '.') . ' pesan',
                'description' => 'Masih ada percakapan client yang belum dibalas.',
                'tone' => 'info',
            ] : null,
            $incomingMeetings > 0 ? [
                'key' => 'incoming-meetings',
                'label' => 'Meeting hari ini',
                'value' => number_format($incomingMeetings, 0, ',', '.') . ' agenda',
                'description' => 'Pastikan meeting notes dan action items siap.',
                'tone' => 'positive',
            ] : null,
            [
                'key' => 'cash-collectible',
                'label' => 'Cash collectible',
                'value' => DashboardFormat::currency(
                    Invoice::query()
                        ->where('workspace_id', $workspace->id)
                        ->whereIn('status', ['sent', 'partial', 'overdue'])
                        ->sum('total'),
                    $currency,
                ),
                'description' => 'Nominal invoice aktif yang masih bisa ditagih.',
                'tone' => 'neutral',
            ],
        ]));
    }

    protected function buildUpcomingMeetings(Workspace $workspace, string $timezone, CarbonInterface $now): array
    {
        return Meeting::query()
            ->where('workspace_id', $workspace->id)
            ->where('scheduled_at', '>=', $now)
            ->with(['project', 'attendees.user'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->map(function (Meeting $meeting) use ($timezone, $now): array {
                $scheduled = $meeting->scheduled_at?->timezone($timezone);

                return [
                    'id' => $meeting->getKey(),
                    'title' => $meeting->title,
                    'when' => DashboardFormat::dayMonthTime($scheduled, $timezone),
                    'participants' => $this->meetingParticipantLabel($meeting),
                    'project' => $meeting->project?->name ?? 'General',
                    'badge' => $this->meetingBadge($scheduled, $now),
                    'badgeTone' => $this->meetingBadgeTone($scheduled, $now),
                ];
            })
            ->all();
    }

    protected function buildCalendar(Workspace $workspace, string $timezone, CarbonInterface $now): array
    {
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $events = CalendarEvent::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn (CalendarEvent $event): string => $event->start_at->timezone($timezone)->toDateString());

        $eventsByDate = [];

        foreach ($events as $date => $items) {
            $eventsByDate[$date] = $items->map(function (CalendarEvent $event) use ($timezone): array {
                return [
                    'id' => $event->getKey(),
                    'title' => $event->title,
                    'time' => DashboardFormat::dayMonthTime($event->start_at, $timezone),
                    'type' => $event->type,
                    'color' => $event->color ?: '#c6811a',
                ];
            })->all();
        }

        return [
            'label' => DashboardFormat::monthYear($now, $timezone),
            'year' => (int) $startOfMonth->year,
            'month' => (int) $startOfMonth->month,
            'today' => $now->toDateString(),
            'daysInMonth' => (int) $startOfMonth->daysInMonth,
            'startWeekday' => (int) $startOfMonth->dayOfWeekIso,
            'eventsByDate' => $eventsByDate,
        ];
    }

    protected function buildRecentFiles(Workspace $workspace, string $timezone): array
    {
        return File::query()
            ->where('workspace_id', $workspace->id)
            ->with('project')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (File $file) use ($timezone): array {
                return [
                    'id' => $file->getKey(),
                    'name' => $file->original_name ?: $file->name,
                    'project' => $file->project?->name ?? 'General',
                    'size' => DashboardFormat::fileSize($file->size_bytes),
                    'when' => DashboardFormat::humanDiff($file->created_at, $timezone),
                    'typeLabel' => $this->fileTypeLabel($file->mime_type),
                    'tone' => $this->fileTone($file->mime_type),
                ];
            })
            ->all();
    }

    protected function revenueSeries(Workspace $workspace, string $timezone, string $filter, array $filters): array
    {
        $labels = [];
        $values = [];
        $formatter = match ($filter) {
            '7d' => fn (CarbonInterface $date): string => DashboardFormat::shortDate($date, $timezone),
            '30d' => fn (CarbonInterface $date): string => DashboardFormat::shortDate($date, $timezone),
            default => fn (CarbonInterface $date): string => DashboardFormat::monthYear($date, $timezone),
        };

        switch ($filter) {
            case '7d':
                for ($offset = 6; $offset >= 0; $offset--) {
                    $date = now()->timezone($timezone)->subDays($offset);
                    $labels[] = $formatter($date);
                    $query = Invoice::query()
                        ->where('workspace_id', $workspace->id)
                        ->where('status', 'paid')
                        ->whereDate('paid_at', $date);
                    $this->applyFilters($query, $filters, 'paid_at');
                    $values[] = (float) $query->sum('total');
                }
                break;

            case '3m':
                for ($offset = 2; $offset >= 0; $offset--) {
                    $date = now()->timezone($timezone)->subMonths($offset);
                    $labels[] = $formatter($date);
                    $query = Invoice::query()
                        ->where('workspace_id', $workspace->id)
                        ->where('status', 'paid')
                        ->whereMonth('paid_at', $date->month)
                        ->whereYear('paid_at', $date->year);
                    $this->applyFilters($query, $filters, 'paid_at');
                    $values[] = (float) $query->sum('total');
                }
                break;

            case '1y':
                for ($offset = 11; $offset >= 0; $offset--) {
                    $date = now()->timezone($timezone)->subMonths($offset);
                    $labels[] = $formatter($date);
                    $query = Invoice::query()
                        ->where('workspace_id', $workspace->id)
                        ->where('status', 'paid')
                        ->whereMonth('paid_at', $date->month)
                        ->whereYear('paid_at', $date->year);
                    $this->applyFilters($query, $filters, 'paid_at');
                    $values[] = (float) $query->sum('total');
                }
                break;

            case '30d':
            default:
                for ($offset = 29; $offset >= 0; $offset--) {
                    $date = now()->timezone($timezone)->subDays($offset);
                    $labels[] = $formatter($date);
                    $query = Invoice::query()
                        ->where('workspace_id', $workspace->id)
                        ->where('status', 'paid')
                        ->whereDate('paid_at', $date);
                    $this->applyFilters($query, $filters, 'paid_at');
                    $values[] = (float) $query->sum('total');
                }
                break;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function leadStageSeries(Workspace $workspace, CarbonInterface $dateLimit, array $filters): array
    {
        $groups = PipelineStage::query()
            ->whereHas('pipeline', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->orderBy('order_index')
            ->get()
            ->groupBy('name');

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($groups as $name => $stages) {
            $labels[] = $name;
            $query = Lead::query()
                ->where('workspace_id', $workspace->id)
                ->whereIn('stage_id', $stages->pluck('id'))
                ->where('created_at', '>=', $dateLimit);
            $this->applyFilters($query, $filters);
            $values[] = $query->count();
            $colors[] = $stages->first()?->color ?: '#ad8a52';
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    protected function metricCard(
        string $key,
        string $label,
        string $value,
        string $icon,
        string $helper,
        float|int $current,
        float|int $previous,
        bool $highIsGood,
    ): array {
        $trend = $this->trendData((float) $current, (float) $previous, $highIsGood);

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'helper' => $helper,
            'trend' => $trend,
        ];
    }

    protected function trendData(float $current, float $previous, bool $highIsGood): array
    {
        if ($previous === 0.0) {
            $difference = $current > 0 ? 100.0 : 0.0;
        } else {
            $difference = (($current - $previous) / $previous) * 100;
        }

        $positive = $difference >= 0;
        $tone = $highIsGood
            ? ($positive ? 'positive' : 'critical')
            : ($positive ? 'critical' : 'positive');

        $verb = $difference === 0.0 ? 'stabil' : ($positive ? 'naik' : 'turun');

        return [
            'value' => DashboardFormat::percent(abs($difference)),
            'label' => DashboardFormat::percent(abs($difference)) . ' ' . $verb . ' vs bulan lalu',
            'tone' => $tone,
        ];
    }

    protected function resolveVisibility(string $role): array
    {
        return match ($role) {
            'owner', 'admin' => [
                'metrics' => ['revenue', 'clients', 'projects', 'tasks', 'invoices', 'automation', 'productivity', 'leads'],
                'charts' => ['revenue', 'leadsConversion', 'projectProgress', 'monthlyGrowth'],
                'categories' => ['crm', 'project', 'finance', 'marketing'],
                'alerts' => ['overdue-tasks', 'overdue-invoices', 'unread-client-messages', 'incoming-meetings', 'cash-collectible'],
            ],
            'project-manager' => [
                'metrics' => ['projects', 'tasks', 'productivity'],
                'charts' => ['projectProgress'],
                'categories' => ['project'],
                'alerts' => ['overdue-tasks', 'incoming-meetings'],
            ],
            'marketing' => [
                'metrics' => ['leads', 'automation'],
                'charts' => ['leadsConversion'],
                'categories' => ['crm', 'marketing'],
                'alerts' => ['unread-client-messages', 'incoming-meetings'],
            ],
            'finance' => [
                'metrics' => ['revenue', 'invoices'],
                'charts' => ['revenue', 'monthlyGrowth'],
                'categories' => ['finance'],
                'alerts' => ['overdue-invoices', 'cash-collectible'],
            ],
            'client' => [
                'metrics' => ['projects'],
                'charts' => ['projectProgress'],
                'categories' => ['project'],
                'alerts' => ['incoming-meetings'],
            ],
            default => [
                'metrics' => ['projects', 'tasks', 'productivity', 'leads'],
                'charts' => ['leadsConversion', 'projectProgress'],
                'categories' => ['crm', 'project'],
                'alerts' => ['overdue-tasks', 'unread-client-messages', 'incoming-meetings'],
            ],
        };
    }

    protected function filterItemsByKey(array $items, array $allowedKeys): array
    {
        return array_values(array_filter($items, function (array $item) use ($allowedKeys): bool {
            return in_array($item['key'] ?? null, $allowedKeys, true);
        }));
    }

    protected function filterChartsByKey(array $charts, array $allowedKeys): array
    {
        return array_intersect_key($charts, array_flip($allowedKeys));
    }

    protected function roleLabel(string $role): string
    {
        return match ($role) {
            'owner' => 'Owner',
            'admin' => 'Admin',
            'project-manager' => 'Project Manager',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'client' => 'Client',
            default => 'Staff',
        };
    }

    protected function activityLabel(ActivityFeed $activity): string
    {
        return match ($activity->type) {
            'create' => 'Data baru dibuat',
            'update' => 'Data diperbarui',
            'delete' => 'Data dihapus',
            'automation' => 'Automation berjalan',
            default => 'Aktivitas workspace',
        };
    }

    protected function activityIcon(ActivityFeed $activity): string
    {
        $subject = class_basename($activity->subject_type ?? '');

        return match ($subject) {
            'Project' => 'briefcase',
            'Invoice', 'Payment' => 'banknotes',
            'Task' => 'check',
            'Client', 'Lead' => 'users',
            default => $activity->type === 'automation' ? 'spark' : 'pulse',
        };
    }

    protected function activityTone(ActivityFeed $activity): string
    {
        return match ($activity->type) {
            'delete' => 'critical',
            'automation' => 'warning',
            'create' => 'positive',
            default => 'neutral',
        };
    }

    protected function meetingParticipantLabel(Meeting $meeting): string
    {
        $participants = $meeting->attendees
            ->map(function ($attendee): string {
                return $attendee->is_external
                    ? ($attendee->external_name ?: 'External')
                    : ($attendee->user?->name ?? 'Internal');
            })
            ->filter()
            ->values();

        if ($participants->isEmpty()) {
            return 'Belum ada peserta';
        }

        if ($participants->count() === 1) {
            return $participants->first();
        }

        return $participants->take(2)->implode(', ') . ' +' . ($participants->count() - 2);
    }

    protected function meetingBadge(?CarbonInterface $scheduled, CarbonInterface $now): string
    {
        if (! $scheduled) {
            return 'Terjadwal';
        }

        if ($scheduled->isSameDay($now)) {
            return 'Hari ini';
        }

        if ($scheduled->isSameDay($now->copy()->addDay())) {
            return 'Besok';
        }

        return 'Mendatang';
    }

    protected function meetingBadgeTone(?CarbonInterface $scheduled, CarbonInterface $now): string
    {
        if (! $scheduled) {
            return 'neutral';
        }

        if ($scheduled->isSameDay($now)) {
            return 'positive';
        }

        if ($scheduled->isSameDay($now->copy()->addDay())) {
            return 'warning';
        }

        return 'neutral';
    }

    protected function fileTypeLabel(?string $mimeType): string
    {
        return match (true) {
            str_contains((string) $mimeType, 'pdf') => 'PDF',
            str_contains((string) $mimeType, 'image') => 'Image',
            str_contains((string) $mimeType, 'video') => 'Video',
            str_contains((string) $mimeType, 'zip') => 'ZIP',
            str_contains((string) $mimeType, 'word') || str_contains((string) $mimeType, 'document') => 'Doc',
            default => 'File',
        };
    }

    protected function fileTone(?string $mimeType): string
    {
        return match ($this->fileTypeLabel($mimeType)) {
            'PDF' => 'critical',
            'Image' => 'positive',
            'Video' => 'warning',
            'ZIP' => 'neutral',
            'Doc' => 'info',
            default => 'neutral',
        };
    }

    protected function applyFilters(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query, array $filters, ?string $dateColumn = 'created_at')
    {
        if (!empty($filters['date_range'])) {
            $range = $this->parseDateRange($filters['date_range']);
            if ($range) {
                $query->whereBetween($dateColumn, [$range['start'], $range['end']]);
            }
        }

        if (!empty($filters['pic_id'])) {
            $query->where(function ($q) use ($filters) {
                $table = $q->getModel()->getTable();
                if ($table === 'users') {
                    $q->where('id', $filters['pic_id']);
                } else {
                    $q->where('assigned_to', $filters['pic_id'])
                      ->orWhere('user_id', $filters['pic_id']);
                }
            });
        }

        if (!empty($filters['category'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('industry', $filters['category'])
                  ->orWhereJsonContains('tags', $filters['category']);
            });
        }

        if (!empty($filters['tier'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('segment', $filters['tier'])
                  ->orWhere('tier', $filters['tier']);
            });
        }

        return $query;
    }

    protected function parseDateRange(string $range): ?array
    {
        $now = now();
        return match ($range) {
            'today' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            '7d' => ['start' => $now->copy()->subDays(7)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            '30d' => ['start' => $now->copy()->subDays(30)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            '90d' => ['start' => $now->copy()->subDays(90)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'year' => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            default => null,
        };
    }

    protected function buildFilterOptions(Workspace $workspace): array
    {
        return [
            'pics' => $workspace->users()->get(['users.id', 'users.name'])->map(fn ($u) => ['value' => $u->id, 'label' => $u->name]),
            'categories' => [
                ['value' => 'Technology', 'label' => 'Teknologi'],
                ['value' => 'Marketing', 'label' => 'Pemasaran'],
                ['value' => 'Design', 'label' => 'Desain'],
                ['value' => 'Development', 'label' => 'Pengembangan'],
            ],
            'tiers' => [
                ['value' => 'enterprise', 'label' => 'Enterprise'],
                ['value' => 'mid-market', 'label' => 'Mid-Market'],
                ['value' => 'small-business', 'label' => 'Small Business'],
            ],
            'datePresets' => [
                ['value' => 'today', 'label' => 'Hari Ini'],
                ['value' => '7d', 'label' => '7 Hari Terakhir'],
                ['value' => '30d', 'label' => '30 Hari Terakhir'],
                ['value' => '90d', 'label' => 'Kuartal Ini'],
                ['value' => 'year', 'label' => 'Tahun Ini'],
            ],
        ];
    }
}
