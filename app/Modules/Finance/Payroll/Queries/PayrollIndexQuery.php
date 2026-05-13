<?php

namespace App\Modules\Finance\Payroll\Queries;

use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\ProjectFinanceSplitItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PayrollIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $splits = $this->splitQuery($workspace, $filters)->get();

        return [
            'splits' => [
                'summary' => $this->buildSummary($splits, $workspace),
                'items' => $splits->map(fn (ProjectFinanceSplit $split): array => $this->transformSplit($split, $workspace))->all(),
                'selected_id' => $filters['split'],
            ],
            'members' => $this->memberReports($workspace),
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'payment_trigger' => $normalize($input['payment_trigger'] ?? null),
            'item_status' => $normalize($input['item_status'] ?? null),
            'split' => $normalize($input['split'] ?? null),
        ];
    }

    protected function splitQuery(Workspace $workspace, array $filters): Builder
    {
        return ProjectFinanceSplit::query()
            ->whereHas('project', fn (Builder $query) => $query->where('workspace_id', $workspace->getKey()))
            ->with([
                'project:id,workspace_id,client_id,name,budget,status',
                'project.client:id,company_name,pic_name',
                'items.user:id,name,email',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('template_name', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn (Builder $itemQuery) => $itemQuery->where('label', 'like', "%{$search}%"));
                });
            })
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['payment_trigger'], fn (Builder $query, string $trigger) => $query->where('payment_trigger', $trigger))
            ->when($filters['item_status'], fn (Builder $query, string $status) => $query->whereHas('items', fn (Builder $itemQuery) => $itemQuery->where('status', $status)))
            ->orderByDesc('updated_at');
    }

    protected function buildSummary(Collection $splits, Workspace $workspace): array
    {
        $allItems = $splits->flatMap->items;
        $teamItems = $allItems->where('type', 'team_fee');
        $pendingAmount = (float) $teamItems->where('status', 'pending')->sum('final_amount');

        return [
            'total_splits' => $splits->count(),
            'team_payout_items' => $teamItems->count(),
            'pending_payout_items' => $teamItems->where('status', 'pending')->count(),
            'paid_payout_items' => $teamItems->where('status', 'paid')->count(),
            'total_operational_cost_label' => $this->currency((float) $splits->sum('total_operational_cost'), $workspace),
            'total_team_fee_label' => $this->currency((float) $splits->sum('total_team_fee'), $workspace),
            'total_kas_kantor_label' => $this->currency((float) $splits->sum('total_kas_kantor'), $workspace),
            'pending_payout_total_label' => $this->currency($pendingAmount, $workspace),
        ];
    }

    protected function transformSplit(ProjectFinanceSplit $split, Workspace $workspace): array
    {
        return [
            'id' => $split->getKey(),
            'project_id' => $split->project_id,
            'template_name' => $split->template_name,
            'kas_kantor_percentage' => (float) $split->kas_kantor_percentage,
            'kas_kantor_amount' => (float) $split->kas_kantor_amount,
            'kas_kantor_amount_label' => $this->currency((float) $split->kas_kantor_amount, $workspace),
            'payment_trigger' => $split->payment_trigger,
            'payment_trigger_label' => $this->paymentTriggerLabel($split->payment_trigger, $split->payment_trigger_custom),
            'payment_trigger_custom' => $split->payment_trigger_custom,
            'total_project_value' => (float) $split->total_project_value,
            'total_project_value_label' => $this->currency((float) $split->total_project_value, $workspace),
            'total_operational_cost' => (float) $split->total_operational_cost,
            'total_operational_cost_label' => $this->currency((float) $split->total_operational_cost, $workspace),
            'total_kas_kantor' => (float) $split->total_kas_kantor,
            'total_kas_kantor_label' => $this->currency((float) $split->total_kas_kantor, $workspace),
            'total_team_fee' => (float) $split->total_team_fee,
            'total_team_fee_label' => $this->currency((float) $split->total_team_fee, $workspace),
            'project' => $split->project ? [
                'id' => $split->project->getKey(),
                'name' => $split->project->name,
                'budget_label' => $this->currency((float) $split->project->budget, $workspace),
                'client_name' => $split->project->client?->company_name,
            ] : null,
            'items' => $split->items->map(fn (ProjectFinanceSplitItem $item): array => [
                'id' => $item->getKey(),
                'type' => $item->type,
                'component_type' => $item->component_type,
                'component_type_label' => $this->componentTypeLabel($item->component_type),
                'label' => $item->label,
                'user_id' => $item->user_id,
                'user' => $item->user ? [
                    'id' => $item->user->getKey(),
                    'name' => $item->user->name,
                ] : null,
                'calculation_type' => $item->calculation_type,
                'calculation_type_label' => ucfirst($item->calculation_type ?? 'flat'),
                'percentage' => $item->percentage !== null ? (float) $item->percentage : null,
                'flat_amount' => $item->flat_amount !== null ? (float) $item->flat_amount : null,
                'flat_amount_label' => $this->currency((float) $item->flat_amount, $workspace),
                'final_amount' => (float) $item->final_amount,
                'final_amount_label' => $this->currency((float) $item->final_amount, $workspace),
                'status' => $item->status,
                'status_label' => ucfirst($item->status ?? 'pending'),
                'paid_at_label' => $item->paid_at?->format('d M Y H:i'),
            ])->values()->all(),
        ];
    }

    protected function memberReports(Workspace $workspace): array
    {
        return ProjectFinanceSplitItem::query()
            ->where('type', 'team_fee')
            ->whereHas('split.project', fn (Builder $query) => $query->where('workspace_id', $workspace->getKey()))
            ->with(['user:id,name', 'split.project:id,name'])
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $items, string $userId) use ($workspace): array {
                $user = $items->first()?->user;
                $pending = (float) $items->where('status', 'pending')->sum('final_amount');
                $paid = (float) $items->where('status', 'paid')->sum('final_amount');

                return [
                    'id' => $userId,
                    'user_name' => $user?->name ?? 'Unassigned',
                    'pending_total_label' => $this->currency($pending, $workspace),
                    'paid_total_label' => $this->currency($paid, $workspace),
                    'total_earning_label' => $this->currency($pending + $paid, $workspace),
                    'commission_total_label' => $this->currency((float) $items->where('component_type', 'commission')->sum('final_amount'), $workspace),
                    'items_count' => $items->count(),
                ];
            })
            ->values()
            ->sortByDesc('items_count')
            ->all();
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])->values()->all(),
            'users' => User::query()
                ->whereHas('workspaceMemberships', fn (Builder $query) => $query->where('workspace_id', $workspace->getKey()))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user): array => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])->values()->all(),
            'paymentTriggers' => [
                ['value' => 'dp', 'label' => 'Saat DP Masuk'],
                ['value' => 'completion', 'label' => 'Saat Project Selesai'],
                ['value' => 'full_paid', 'label' => 'Saat Client Lunas'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'itemStatuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'paid', 'label' => 'Paid'],
            ],
            'componentTypes' => [
                ['value' => 'operational', 'label' => 'Operational Cost'],
                ['value' => 'base_fee', 'label' => 'Base Fee'],
                ['value' => 'bonus', 'label' => 'Bonus Project'],
                ['value' => 'commission', 'label' => 'Closing Commission'],
                ['value' => 'deduction', 'label' => 'Deduction'],
            ],
        ];
    }

    protected function paymentTriggerLabel(?string $value, ?string $custom): string
    {
        return match ($value) {
            'dp' => 'Saat DP Masuk',
            'completion' => 'Saat Project Selesai',
            'full_paid' => 'Saat Client Lunas',
            'custom' => $custom ?: 'Custom',
            default => 'Not set',
        };
    }

    protected function componentTypeLabel(?string $value): string
    {
        return match ($value) {
            'operational' => 'Operational Cost',
            'bonus' => 'Bonus Project',
            'commission' => 'Closing Commission',
            'deduction' => 'Deduction',
            default => 'Base Fee',
        };
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
