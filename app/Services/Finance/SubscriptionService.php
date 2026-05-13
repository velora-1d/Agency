<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class SubscriptionService
{
    public function createSubscription(Workspace $workspace, array $data): Subscription
    {
        $vendor = $this->resolveVendor($workspace, $data['vendor_id'] ?? null);
        $transaction = $this->resolveExpenseTransaction($workspace, $data['transaction_id'] ?? null);

        $subscription = Subscription::query()->create([
            'workspace_id' => $workspace->getKey(),
            'vendor_id' => $vendor?->getKey(),
            'transaction_id' => $transaction?->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'status' => $data['status'] ?? 'active',
            'next_renewal_date' => $data['next_renewal_date'] ?? null,
            'reminder_days_before' => $data['reminder_days_before'] ?? 7,
        ]);

        $this->logActivity($workspace, $subscription, sprintf('Subscription %s berhasil dibuat.', $subscription->name), 'create', 'emerald');

        return $subscription->refresh()->load(['vendor', 'transaction.account']);
    }

    public function updateSubscription(Workspace $workspace, Subscription $subscription, array $data): Subscription
    {
        abort_unless($subscription->workspace_id === $workspace->getKey(), 404);

        $vendor = $this->resolveVendor($workspace, $data['vendor_id'] ?? null);
        $transaction = $this->resolveExpenseTransaction($workspace, $data['transaction_id'] ?? null);

        $subscription->update([
            'vendor_id' => $vendor?->getKey(),
            'transaction_id' => $transaction?->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'status' => $data['status'] ?? $subscription->status,
            'next_renewal_date' => $data['next_renewal_date'] ?? null,
            'reminder_days_before' => $data['reminder_days_before'] ?? 7,
        ]);

        $this->logActivity($workspace, $subscription, sprintf('Subscription %s diperbarui.', $subscription->name), 'update', 'amber');

        return $subscription->refresh()->load(['vendor', 'transaction.account']);
    }

    public function deleteSubscription(Workspace $workspace, Subscription $subscription): void
    {
        abort_unless($subscription->workspace_id === $workspace->getKey(), 404);

        $name = $subscription->name;
        $subscription->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Subscription::class,
            'subject_id' => null,
            'description' => sprintf('Subscription %s dihapus.', $name),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateSubscriptionStatus(Workspace $workspace, Subscription $subscription, string $status): Subscription
    {
        abort_unless($subscription->workspace_id === $workspace->getKey(), 404);

        $subscription->update([
            'status' => $status,
        ]);

        $this->logActivity($workspace, $subscription, sprintf('Status subscription %s diubah ke %s.', $subscription->name, $status), 'status', 'sky');

        return $subscription->refresh()->load(['vendor', 'transaction.account']);
    }

    public function createVendor(Workspace $workspace, array $data): Vendor
    {
        $vendor = Vendor::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'contract' => $data['contract'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logVendorActivity($workspace, $vendor, sprintf('Vendor %s berhasil dibuat.', $vendor->name), 'create', 'emerald');

        return $vendor->refresh();
    }

    public function updateVendor(Workspace $workspace, Vendor $vendor, array $data): Vendor
    {
        abort_unless($vendor->workspace_id === $workspace->getKey(), 404);

        $vendor->update([
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'contract' => $data['contract'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logVendorActivity($workspace, $vendor, sprintf('Vendor %s diperbarui.', $vendor->name), 'update', 'amber');

        return $vendor->refresh();
    }

    public function deleteVendor(Workspace $workspace, Vendor $vendor): void
    {
        abort_unless($vendor->workspace_id === $workspace->getKey(), 404);

        $name = $vendor->name;
        $vendor->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Vendor::class,
            'subject_id' => null,
            'description' => sprintf('Vendor %s dihapus.', $name),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    protected function resolveVendor(Workspace $workspace, ?string $vendorId): ?Vendor
    {
        if (! $vendorId) {
            return null;
        }

        return Vendor::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($vendorId);
    }

    protected function resolveExpenseTransaction(Workspace $workspace, ?string $transactionId): ?Transaction
    {
        if (! $transactionId) {
            return null;
        }

        return Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'expense')
            ->findOrFail($transactionId);
    }

    protected function logActivity(
        Workspace $workspace,
        Subscription $subscription,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Subscription::class,
            'subject_id' => $subscription->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'RefreshCw',
                    'update' => 'Pencil',
                    default => 'Clock3',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }

    protected function logVendorActivity(
        Workspace $workspace,
        Vendor $vendor,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Vendor::class,
            'subject_id' => $vendor->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'Building2',
                    'update' => 'Pencil',
                    default => 'BriefcaseBusiness',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
