<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\ProjectFinanceSplitItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        protected FinanceSplitService $financeSplitService
    ) {
    }

    public function create(Workspace $workspace, array $data): ProjectFinanceSplit
    {
        return DB::transaction(function () use ($workspace, $data): ProjectFinanceSplit {
            $project = $this->resolveProject($workspace, $data['project_id']);

            $split = ProjectFinanceSplit::query()->create([
                'project_id' => $project->getKey(),
                'template_name' => $data['template_name'] ?? null,
                'kas_kantor_percentage' => $data['kas_kantor_percentage'] ?? 0,
                'payment_trigger' => $data['payment_trigger'] ?? null,
                'payment_trigger_custom' => $data['payment_trigger_custom'] ?? null,
                'total_project_value' => $project->budget ?? 0,
            ]);

            $this->syncItems($split, $workspace, $data['items']);
            $this->financeSplitService->calculate($split->load('items', 'project.invoices'));

            $this->logActivity($workspace, $split, sprintf('Payroll split %s berhasil dibuat.', $project->name), 'create', 'emerald');

            return $split->refresh()->load(['project.client', 'items.user']);
        });
    }

    public function update(Workspace $workspace, ProjectFinanceSplit $split, array $data): ProjectFinanceSplit
    {
        abort_unless($split->project?->workspace_id === $workspace->getKey(), 404);

        return DB::transaction(function () use ($workspace, $split, $data): ProjectFinanceSplit {
            $project = $this->resolveProject($workspace, $data['project_id']);

            $split->update([
                'project_id' => $project->getKey(),
                'template_name' => $data['template_name'] ?? null,
                'kas_kantor_percentage' => $data['kas_kantor_percentage'] ?? 0,
                'payment_trigger' => $data['payment_trigger'] ?? null,
                'payment_trigger_custom' => $data['payment_trigger_custom'] ?? null,
                'total_project_value' => $project->budget ?? $split->total_project_value,
            ]);

            $split->items()->delete();
            $this->syncItems($split, $workspace, $data['items']);
            $this->financeSplitService->calculate($split->load('items', 'project.invoices'));

            $this->logActivity($workspace, $split, sprintf('Payroll split %s diperbarui.', $project->name), 'update', 'amber');

            return $split->refresh()->load(['project.client', 'items.user']);
        });
    }

    public function delete(Workspace $workspace, ProjectFinanceSplit $split): void
    {
        abort_unless($split->project?->workspace_id === $workspace->getKey(), 404);

        $projectName = $split->project?->name ?? 'project';
        $split->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => ProjectFinanceSplit::class,
            'subject_id' => null,
            'description' => sprintf('Payroll split %s dihapus.', $projectName),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateItemStatus(
        Workspace $workspace,
        ProjectFinanceSplit $split,
        ProjectFinanceSplitItem $item,
        string $status
    ): ProjectFinanceSplitItem {
        abort_unless($split->project?->workspace_id === $workspace->getKey(), 404);
        abort_unless($item->split_id === $split->getKey(), 404);

        $item->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        $this->logActivity($workspace, $split, sprintf('Status payout %s diubah ke %s.', $item->label, $status), 'status', 'sky');

        return $item->refresh()->load('user');
    }

    protected function syncItems(ProjectFinanceSplit $split, Workspace $workspace, array $items): void
    {
        foreach (collect($items)->values() as $item) {
            $user = $this->resolveUser($workspace, $item['user_id'] ?? null);

            $split->items()->create([
                'type' => $item['type'],
                'component_type' => $item['component_type'] ?? ($item['type'] === 'operational' ? 'operational' : 'base_fee'),
                'label' => $item['label'],
                'user_id' => $user?->getKey(),
                'calculation_type' => $item['type'] === 'team_fee' ? ($item['calculation_type'] ?? 'flat') : 'flat',
                'percentage' => $item['type'] === 'team_fee' ? ($item['percentage'] ?? null) : null,
                'flat_amount' => $item['flat_amount'] ?? null,
                'status' => $item['status'] ?? 'pending',
            ]);
        }
    }

    protected function resolveProject(Workspace $workspace, string $projectId): Project
    {
        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($projectId);
    }

    protected function resolveUser(Workspace $workspace, ?string $userId): ?User
    {
        if (! $userId) {
            return null;
        }

        return User::query()
            ->whereHas('workspaceMemberships', fn ($query) => $query->where('workspace_id', $workspace->getKey()))
            ->findOrFail($userId);
    }

    protected function logActivity(
        Workspace $workspace,
        ProjectFinanceSplit $split,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => ProjectFinanceSplit::class,
            'subject_id' => $split->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'WalletCards',
                    'update' => 'Pencil',
                    default => 'Clock3',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
