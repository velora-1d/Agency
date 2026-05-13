<?php

namespace App\Modules\Finance\Reports\Queries;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\DepartmentBudget;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectFinanceSplitItem;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinancialReportIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        [$start, $end] = $this->resolvePeriod($filters['period']);

        return [
            'reports' => [
                'summary' => $this->summary($workspace, $start, $end),
                'profitLoss' => $this->profitLoss($workspace, $start, $end),
                'cashFlow' => $this->cashFlow($workspace, $start, $end),
                'balanceSheet' => $this->balanceSheet($workspace),
                'projectCosts' => $this->projectCosts($workspace, $start, $end),
                'taxReport' => $this->taxReport($workspace, $start, $end),
                'divisionReports' => $this->divisionReports($workspace, $start, $end),
                'employeeReports' => $this->employeeReports($workspace, $start, $end),
            ],
            'chartOfAccounts' => [
                'summary' => $this->chartSummary($workspace),
                'items' => $this->chartItems($workspace),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'periods' => [
                    ['value' => 'month', 'label' => 'This Month'],
                    ['value' => 'quarter', 'label' => 'This Quarter'],
                    ['value' => 'year', 'label' => 'This Year'],
                ],
                'accountTypes' => [
                    ['value' => 'asset', 'label' => 'Asset'],
                    ['value' => 'liability', 'label' => 'Liability'],
                    ['value' => 'equity', 'label' => 'Equity'],
                    ['value' => 'revenue', 'label' => 'Revenue'],
                    ['value' => 'expense', 'label' => 'Expense'],
                ],
            ],
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $period = $input['period'] ?? 'month';

        return [
            'period' => in_array($period, ['month', 'quarter', 'year'], true) ? $period : 'month',
        ];
    }

    protected function resolvePeriod(string $period): array
    {
        return match ($period) {
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    protected function summary(Workspace $workspace, $start, $end): array
    {
        $income = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'income')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $expense = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        return [
            'income_label' => $this->currency($income, $workspace),
            'expense_label' => $this->currency($expense, $workspace),
            'profit_label' => $this->currency($income - $expense, $workspace),
            'active_coa_count' => ChartOfAccount::query()->where('workspace_id', $workspace->getKey())->where('is_active', true)->count(),
        ];
    }

    protected function profitLoss(Workspace $workspace, $start, $end): array
    {
        $income = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'income')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $expense = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        return [
            'revenue_label' => $this->currency($income, $workspace),
            'expense_label' => $this->currency($expense, $workspace),
            'gross_profit_label' => $this->currency($income - $expense, $workspace),
        ];
    }

    protected function cashFlow(Workspace $workspace, $start, $end): array
    {
        $transactions = Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        return [
            'inflow_label' => $this->currency((float) $transactions->where('type', 'income')->sum('amount'), $workspace),
            'outflow_label' => $this->currency((float) $transactions->where('type', 'expense')->sum('amount'), $workspace),
            'net_flow_label' => $this->currency((float) $transactions->where('type', 'income')->sum('amount') - (float) $transactions->where('type', 'expense')->sum('amount'), $workspace),
        ];
    }

    protected function balanceSheet(Workspace $workspace): array
    {
        $assets = (float) BankAccount::query()->where('workspace_id', $workspace->getKey())->sum('balance');
        $liabilities = (float) Reimbursement::query()->where('workspace_id', $workspace->getKey())->where('status', 'approved')->sum('amount');
        $equity = $assets - $liabilities;

        return [
            'assets_label' => $this->currency($assets, $workspace),
            'liabilities_label' => $this->currency($liabilities, $workspace),
            'equity_label' => $this->currency($equity, $workspace),
        ];
    }

    protected function projectCosts(Workspace $workspace, $start, $end): array
    {
        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['client:id,company_name'])
            ->orderBy('name')
            ->get()
            ->map(function (Project $project) use ($workspace, $start, $end): array {
                $expense = (float) Transaction::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('project_id', $project->getKey())
                    ->where('type', 'expense')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->sum('amount');

                $teamFee = (float) ProjectFinanceSplitItem::query()
                    ->whereHas('split', fn (Builder $query) => $query->where('project_id', $project->getKey()))
                    ->sum('final_amount');

                return [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                    'client_name' => $project->client?->company_name,
                    'hpp_label' => $this->currency($expense + $teamFee, $workspace),
                    'expense_label' => $this->currency($expense, $workspace),
                    'team_fee_label' => $this->currency($teamFee, $workspace),
                ];
            })
            ->values()
            ->all();
    }

    protected function taxReport(Workspace $workspace, $start, $end): array
    {
        $ppn = (float) Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereBetween('created_at', [$start, $end])
            ->sum('tax_amount');

        $pph21 = (float) ProjectFinanceSplitItem::query()
            ->where('type', 'team_fee')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('final_amount') * 0.05;

        $pph23 = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'expense')
            ->whereIn('category', ['tools', 'subscription', 'vendor', 'operational'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount') * 0.02;

        return [
            'ppn_label' => $this->currency($ppn, $workspace),
            'pph21_label' => $this->currency($pph21, $workspace),
            'pph23_label' => $this->currency($pph23, $workspace),
        ];
    }

    protected function divisionReports(Workspace $workspace, $start, $end): array
    {
        return Reimbursement::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('department')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy('department')
            ->map(function (Collection $items, string $department) use ($workspace): array {
                $paid = (float) $items->where('status', 'paid')->sum('amount');
                $budget = (float) DepartmentBudget::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('department', $department)
                    ->sum('limit_amount');

                return [
                    'department' => $department,
                    'spent_label' => $this->currency($paid, $workspace),
                    'budget_label' => $this->currency($budget, $workspace),
                    'variance_label' => $this->currency($budget - $paid, $workspace),
                ];
            })
            ->values()
            ->all();
    }

    protected function employeeReports(Workspace $workspace, $start, $end): array
    {
        return User::query()
            ->whereHas('workspaceMemberships', fn (Builder $query) => $query->where('workspace_id', $workspace->getKey()))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (User $user) use ($workspace, $start, $end): array {
                $earned = (float) ProjectFinanceSplitItem::query()
                    ->where('user_id', $user->getKey())
                    ->where('type', 'team_fee')
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('final_amount');

                $claims = (float) Reimbursement::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('user_id', $user->getKey())
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('amount');

                return [
                    'user_name' => $user->name,
                    'earning_label' => $this->currency($earned, $workspace),
                    'claims_label' => $this->currency($claims, $workspace),
                    'net_label' => $this->currency($earned - $claims, $workspace),
                ];
            })
            ->values()
            ->all();
    }

    protected function chartSummary(Workspace $workspace): array
    {
        return [
            'total_accounts' => ChartOfAccount::query()->where('workspace_id', $workspace->getKey())->count(),
            'active_accounts' => ChartOfAccount::query()->where('workspace_id', $workspace->getKey())->where('is_active', true)->count(),
        ];
    }

    protected function chartItems(Workspace $workspace): array
    {
        return ChartOfAccount::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('code')
            ->get()
            ->map(fn (ChartOfAccount $account): array => [
                'id' => $account->getKey(),
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'type_label' => ucfirst($account->type),
                'category' => $account->category,
                'is_active' => (bool) $account->is_active,
                'is_active_label' => $account->is_active ? 'Active' : 'Inactive',
                'notes' => $account->notes,
            ])
            ->values()
            ->all();
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
