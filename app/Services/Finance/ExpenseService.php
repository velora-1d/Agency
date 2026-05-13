<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\BankAccount;
use App\Models\DepartmentBudget;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function createReimbursement(Workspace $workspace, array $data): Reimbursement
    {
        $reimbursement = Reimbursement::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => $data['user_id'] ?? Auth::id(),
            'project_id' => $this->resolveProject($workspace, $data['project_id'] ?? null)?->getKey(),
            'department' => $data['department'] ?? null,
            'title' => $data['title'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'receipt_path' => $data['receipt_path'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_account_id' => $data['paid_account_id'] ?? null,
        ]);

        $this->logActivity($workspace, $reimbursement, sprintf('Reimbursement %s dibuat.', $reimbursement->title), 'create', 'emerald');

        return $reimbursement->refresh()->load(['user:id,name', 'project:id,name', 'paidAccount:id,name']);
    }

    public function updateReimbursement(Workspace $workspace, Reimbursement $reimbursement, array $data): Reimbursement
    {
        abort_unless($reimbursement->workspace_id === $workspace->getKey(), 404);

        $reimbursement->update([
            'user_id' => $data['user_id'] ?? $reimbursement->user_id,
            'project_id' => $this->resolveProject($workspace, $data['project_id'] ?? null)?->getKey(),
            'department' => $data['department'] ?? null,
            'title' => $data['title'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? $reimbursement->status,
            'receipt_path' => $data['receipt_path'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_account_id' => $data['paid_account_id'] ?? $reimbursement->paid_account_id,
        ]);

        $this->logActivity($workspace, $reimbursement, sprintf('Reimbursement %s diperbarui.', $reimbursement->title), 'update', 'amber');

        return $reimbursement->refresh()->load(['user:id,name', 'project:id,name', 'paidAccount:id,name']);
    }

    public function deleteReimbursement(Workspace $workspace, Reimbursement $reimbursement): void
    {
        abort_unless($reimbursement->workspace_id === $workspace->getKey(), 404);

        $title = $reimbursement->title;
        $reimbursement->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Reimbursement::class,
            'subject_id' => null,
            'description' => sprintf('Reimbursement %s dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateStatus(Workspace $workspace, Reimbursement $reimbursement, string $status, ?string $paidAccountId = null): Reimbursement
    {
        abort_unless($reimbursement->workspace_id === $workspace->getKey(), 404);

        return DB::transaction(function () use ($workspace, $reimbursement, $status, $paidAccountId): Reimbursement {
            $payload = ['status' => $status];

            if ($status === 'approved') {
                $payload['approved_by'] = Auth::id();
                $payload['approved_at'] = now();
            }

            if ($status === 'rejected') {
                $payload['approved_by'] = Auth::id();
                $payload['approved_at'] = now();
                $payload['paid_at'] = null;
            }

            if ($status === 'paid') {
                $account = $this->resolveAccount($workspace, $paidAccountId ?? $reimbursement->paid_account_id);
                $payload['paid_account_id'] = $account?->getKey();
                $payload['paid_at'] = now();

                if ($reimbursement->paid_at === null) {
                    Transaction::query()->create([
                        'workspace_id' => $workspace->getKey(),
                        'account_id' => $account?->getKey(),
                        'type' => 'expense',
                        'category' => 'reimbursement',
                        'amount' => $reimbursement->amount,
                        'description' => sprintf('Reimbursement %s', $reimbursement->title),
                        'date' => now()->toDateString(),
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }

            $reimbursement->update($payload);
            $this->logActivity($workspace, $reimbursement, sprintf('Status reimbursement %s diubah ke %s.', $reimbursement->title, $status), 'status', 'sky');

            return $reimbursement->refresh()->load(['user:id,name', 'project:id,name', 'paidAccount:id,name']);
        });
    }

    public function createBudget(Workspace $workspace, array $data): DepartmentBudget
    {
        $budget = DepartmentBudget::query()->create([
            'workspace_id' => $workspace->getKey(),
            'department' => $data['department'],
            'period_label' => $data['period_label'],
            'limit_amount' => $data['limit_amount'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logBudgetActivity($workspace, $budget, sprintf('Budget divisi %s berhasil dibuat.', $budget->department), 'create', 'emerald');

        return $budget->refresh();
    }

    public function updateBudget(Workspace $workspace, DepartmentBudget $budget, array $data): DepartmentBudget
    {
        abort_unless($budget->workspace_id === $workspace->getKey(), 404);

        $budget->update([
            'department' => $data['department'],
            'period_label' => $data['period_label'],
            'limit_amount' => $data['limit_amount'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logBudgetActivity($workspace, $budget, sprintf('Budget divisi %s diperbarui.', $budget->department), 'update', 'amber');

        return $budget->refresh();
    }

    public function deleteBudget(Workspace $workspace, DepartmentBudget $budget): void
    {
        abort_unless($budget->workspace_id === $workspace->getKey(), 404);

        $department = $budget->department;
        $budget->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => DepartmentBudget::class,
            'subject_id' => null,
            'description' => sprintf('Budget divisi %s dihapus.', $department),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    protected function resolveProject(Workspace $workspace, ?string $projectId): ?Project
    {
        if (! $projectId) {
            return null;
        }

        return Project::query()->where('workspace_id', $workspace->getKey())->findOrFail($projectId);
    }

    protected function resolveAccount(Workspace $workspace, ?string $accountId): ?BankAccount
    {
        if (! $accountId) {
            return null;
        }

        return BankAccount::query()->where('workspace_id', $workspace->getKey())->findOrFail($accountId);
    }

    protected function logActivity(Workspace $workspace, Reimbursement $reimbursement, string $description, string $action, string $color): ActivityFeed
    {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Reimbursement::class,
            'subject_id' => $reimbursement->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'Receipt',
                    'update' => 'Pencil',
                    'status' => 'Clock3',
                    default => 'Banknote',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }

    protected function logBudgetActivity(Workspace $workspace, DepartmentBudget $budget, string $description, string $action, string $color): ActivityFeed
    {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => DepartmentBudget::class,
            'subject_id' => $budget->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FolderKanban',
                    'update' => 'Pencil',
                    default => 'ReceiptText',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
