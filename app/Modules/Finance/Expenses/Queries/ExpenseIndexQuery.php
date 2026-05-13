<?php

namespace App\Modules\Finance\Expenses\Queries;

use App\Models\BankAccount;
use App\Models\DepartmentBudget;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpenseIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        return [
            'reimbursements' => [
                'summary' => $this->buildSummary($workspace),
                'items' => $this->reimbursementQuery($workspace, $filters)->get()->map(fn (Reimbursement $reimbursement): array => $this->transformReimbursement($reimbursement, $workspace))->values()->all(),
                'selected_id' => $filters['reimbursement'],
            ],
            'budgets' => [
                'summary' => $this->budgetSummary($workspace),
                'items' => $this->budgetItems($workspace),
            ],
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'department' => $normalize($input['department'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'reimbursement' => $normalize($input['reimbursement'] ?? null),
        ];
    }

    protected function reimbursementQuery(Workspace $workspace, array $filters): Builder
    {
        return Reimbursement::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['user:id,name', 'project:id,name', 'paidAccount:id,name', 'approver:id,name'])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['department'], fn (Builder $query, string $department) => $query->where('department', $department))
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->orderByDesc('created_at');
    }

    protected function buildSummary(Workspace $workspace): array
    {
        $reimbursements = Reimbursement::query()->where('workspace_id', $workspace->getKey())->get();
        $approvedPaid = $reimbursements->whereIn('status', ['approved', 'paid']);

        return [
            'total_reimbursements' => $reimbursements->count(),
            'pending_reimbursements' => $reimbursements->where('status', 'pending')->count(),
            'approved_reimbursements' => $reimbursements->where('status', 'approved')->count(),
            'paid_reimbursements' => $reimbursements->where('status', 'paid')->count(),
            'total_reimbursement_amount_label' => $this->currency((float) $approvedPaid->sum('amount'), $workspace),
            'petty_cash_balance_label' => $this->currency((float) BankAccount::query()->where('workspace_id', $workspace->getKey())->where('type', 'cash')->sum('balance'), $workspace),
            'budget_alerts' => $this->budgetAlerts($workspace)->count(),
        ];
    }

    protected function transformReimbursement(Reimbursement $reimbursement, Workspace $workspace): array
    {
        return [
            'id' => $reimbursement->getKey(),
            'user_id' => $reimbursement->user_id,
            'project_id' => $reimbursement->project_id,
            'department' => $reimbursement->department,
            'title' => $reimbursement->title,
            'amount' => (float) $reimbursement->amount,
            'amount_label' => $this->currency((float) $reimbursement->amount, $workspace),
            'status' => $reimbursement->status,
            'status_label' => ucfirst($reimbursement->status),
            'receipt_path' => $reimbursement->receipt_path,
            'notes' => $reimbursement->notes,
            'approved_at_label' => $reimbursement->approved_at?->format('d M Y H:i'),
            'paid_at_label' => $reimbursement->paid_at?->format('d M Y H:i'),
            'user' => $reimbursement->user ? [
                'id' => $reimbursement->user->getKey(),
                'name' => $reimbursement->user->name,
            ] : null,
            'project' => $reimbursement->project ? [
                'id' => $reimbursement->project->getKey(),
                'name' => $reimbursement->project->name,
            ] : null,
            'paid_account' => $reimbursement->paidAccount ? [
                'id' => $reimbursement->paidAccount->getKey(),
                'name' => $reimbursement->paidAccount->name,
            ] : null,
            'approver' => $reimbursement->approver ? [
                'id' => $reimbursement->approver->getKey(),
                'name' => $reimbursement->approver->name,
            ] : null,
            'budget_status' => $this->budgetStatus($workspace, $reimbursement),
        ];
    }

    protected function budgetItems(Workspace $workspace): array
    {
        return DepartmentBudget::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('department')
            ->get()
            ->map(function (DepartmentBudget $budget) use ($workspace): array {
                $spent = (float) Reimbursement::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('department', $budget->department)
                    ->where('status', 'paid')
                    ->sum('amount');

                return [
                    'id' => $budget->getKey(),
                    'department' => $budget->department,
                    'period_label' => $budget->period_label,
                    'limit_amount_label' => $this->currency((float) $budget->limit_amount, $workspace),
                    'spent_amount_label' => $this->currency($spent, $workspace),
                    'remaining_amount_label' => $this->currency(max(0, (float) $budget->limit_amount - $spent), $workspace),
                    'notes' => $budget->notes,
                    'over_budget' => $spent > (float) $budget->limit_amount,
                ];
            })
            ->values()
            ->all();
    }

    protected function budgetSummary(Workspace $workspace): array
    {
        return [
            'total_budgets' => DepartmentBudget::query()->where('workspace_id', $workspace->getKey())->count(),
            'over_budget_count' => $this->budgetAlerts($workspace)->count(),
        ];
    }

    protected function budgetAlerts(Workspace $workspace): Collection
    {
        return DepartmentBudget::query()
            ->where('workspace_id', $workspace->getKey())
            ->get()
            ->filter(function (DepartmentBudget $budget) use ($workspace): bool {
                $spent = (float) Reimbursement::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('department', $budget->department)
                    ->where('status', 'paid')
                    ->sum('amount');

                return $spent > (float) $budget->limit_amount;
            });
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'users' => User::query()
                ->whereHas('workspaceMemberships', fn (Builder $query) => $query->where('workspace_id', $workspace->getKey()))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user): array => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])->values()->all(),
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])->values()->all(),
            'accounts' => BankAccount::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('type', 'cash')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BankAccount $account): array => [
                    'id' => $account->getKey(),
                    'name' => $account->name,
                ])->values()->all(),
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
                ['value' => 'paid', 'label' => 'Paid'],
            ],
            'departments' => Reimbursement::query()
                ->where('workspace_id', $workspace->getKey())
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department')
                ->map(fn (string $department): array => [
                    'value' => $department,
                    'label' => $department,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function budgetStatus(Workspace $workspace, Reimbursement $reimbursement): string
    {
        if (! $reimbursement->department) {
            return 'no_department';
        }

        $budget = DepartmentBudget::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('department', $reimbursement->department)
            ->first();

        if (! $budget) {
            return 'no_budget';
        }

        $spent = (float) Reimbursement::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('department', $budget->department)
            ->where('status', 'paid')
            ->sum('amount');

        return $spent > (float) $budget->limit_amount ? 'over_budget' : 'within_budget';
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
