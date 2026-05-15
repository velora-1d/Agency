<?php

namespace App\Modules\System\Executive\Queries;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExecutiveHubQuery
{
    public function getHubPayload(User $user, array $input = []): array
    {
        $workspaces = $user->workspaces()->get(['workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspaces.currency']);
        $workspaceIds = $workspaces->pluck('id')->all();

        return [
            'summary' => $this->aggregateSummary($workspaceIds),
            'brandPerformance' => $this->brandPerformance($workspaces),
            'revenueTrend' => $this->revenueTrend($workspaceIds),
            'workspaces' => $workspaces->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'slug' => $w->slug,
            ])->values()->all(),
        ];
    }

    protected function aggregateSummary(array $workspaceIds): array
    {
        $totalRevenue = (float) Transaction::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = (float) Transaction::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->where('type', 'expense')
            ->sum('amount');

        $activeProjects = Project::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->whereIn('status', ['active', 'planning'])
            ->count();

        $unpaidInvoices = Invoice::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('total');

        return [
            'total_revenue_label' => $this->formatCurrency($totalRevenue),
            'total_expense_label' => $this->formatCurrency($totalExpense),
            'net_profit_label' => $this->formatCurrency($totalRevenue - $totalExpense),
            'active_projects_count' => $activeProjects,
            'receivables_label' => $this->formatCurrency($unpaidInvoices),
            'profit_margin' => $totalRevenue > 0 ? round((($totalRevenue - $totalExpense) / $totalRevenue) * 100, 1) : 0,
        ];
    }

    protected function brandPerformance(Collection $workspaces): array
    {
        return $workspaces->map(function ($workspace) {
            $revenue = (float) Transaction::query()
                ->where('workspace_id', $workspace->id)
                ->where('type', 'income')
                ->sum('amount');

            $expense = (float) Transaction::query()
                ->where('workspace_id', $workspace->id)
                ->where('type', 'expense')
                ->sum('amount');

            return [
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'revenue' => $revenue,
                'revenue_label' => $this->formatCurrency($revenue),
                'profit' => $revenue - $expense,
                'profit_label' => $this->formatCurrency($revenue - $expense),
                'projects_count' => Project::query()->where('workspace_id', $workspace->id)->count(),
            ];
        })->values()->all();
    }

    protected function revenueTrend(array $workspaceIds): array
    {
        // Simple monthly trend for the last 6 months
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            $amount = (float) Transaction::query()
                ->whereIn('workspace_id', $workspaceIds)
                ->where('type', 'income')
                ->whereBetween('date', [$start, $end])
                ->sum('amount');

            $trend[] = [
                'month' => $month->translatedFormat('M'),
                'amount' => $amount,
                'label' => $this->formatCurrency($amount),
            ];
        }

        return $trend;
    }

    protected function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
