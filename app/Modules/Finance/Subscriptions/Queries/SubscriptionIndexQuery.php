<?php

namespace App\Modules\Finance\Subscriptions\Queries;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SubscriptionIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $subscriptions = $this->applyCollectionFilters(
            $this->subscriptionQuery($workspace, $filters)->get(),
            $filters,
        )->values();

        return [
            'subscriptions' => [
                'summary' => $this->buildSummary($subscriptions, $workspace),
                'items' => $subscriptions->map(fn (Subscription $subscription): array => $this->transformSubscription($subscription, $workspace))->all(),
                'selected_id' => $filters['subscription'],
            ],
            'vendors' => [
                'summary' => $this->buildVendorSummary($workspace),
                'items' => $this->vendorItems($workspace),
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
            'vendor' => $normalize($input['vendor'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'billing_cycle' => $normalize($input['billing_cycle'] ?? null),
            'renewal_state' => $normalize($input['renewal_state'] ?? null),
            'subscription' => $normalize($input['subscription'] ?? null),
        ];
    }

    protected function subscriptionQuery(Workspace $workspace, array $filters): Builder
    {
        return Subscription::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'vendor:id,name,contact_name,email,phone,contract,payment_terms,notes',
                'transaction:id,account_id,category,amount,date,description,type',
                'transaction.account:id,name',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('vendor', fn (Builder $vendorQuery) => $vendorQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery
                            ->where('category', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%"));
                });
            })
            ->when($filters['vendor'], fn (Builder $query, string $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['billing_cycle'], fn (Builder $query, string $billingCycle) => $query->where('billing_cycle', $billingCycle))
            ->orderByRaw("CASE WHEN next_renewal_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('next_renewal_date')
            ->orderByDesc('created_at');
    }

    protected function applyCollectionFilters(Collection $subscriptions, array $filters): Collection
    {
        if (! $filters['renewal_state']) {
            return $subscriptions;
        }

        return $subscriptions->filter(function (Subscription $subscription) use ($filters): bool {
            return $this->renewalState($subscription) === $filters['renewal_state'];
        });
    }

    protected function buildSummary(Collection $subscriptions, Workspace $workspace): array
    {
        $monthlyCash = (float) $subscriptions->sum(fn (Subscription $subscription): float => $this->monthlyEquivalent($subscription));
        $annualCommitment = (float) $subscriptions->sum(fn (Subscription $subscription): float => $this->annualizedAmount($subscription));
        $effectiveStatuses = $subscriptions->map(fn (Subscription $subscription): string => $this->effectiveStatus($subscription));

        return [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $effectiveStatuses->filter(fn (string $status): bool => $status === 'active')->count(),
            'expired_subscriptions' => $effectiveStatuses->filter(fn (string $status): bool => $status === 'expired')->count(),
            'cancelled_subscriptions' => $effectiveStatuses->filter(fn (string $status): bool => $status === 'cancelled')->count(),
            'due_soon_subscriptions' => $subscriptions->filter(fn (Subscription $subscription): bool => $this->renewalState($subscription) === 'due_soon')->count(),
            'linked_expense_count' => $subscriptions->whereNotNull('transaction_id')->count(),
            'monthly_cash_total' => $monthlyCash,
            'monthly_cash_total_label' => $this->currency($monthlyCash, $workspace),
            'annual_commitment_total' => $annualCommitment,
            'annual_commitment_total_label' => $this->currency($annualCommitment, $workspace),
            'upcoming_renewal_label' => $subscriptions
                ->filter(fn (Subscription $subscription): bool => $subscription->next_renewal_date !== null)
                ->sortBy('next_renewal_date')
                ->first()?->next_renewal_date?->format('d M Y') ?? 'No renewal date',
        ];
    }

    protected function buildVendorSummary(Workspace $workspace): array
    {
        $vendorCount = Vendor::query()
            ->where('workspace_id', $workspace->getKey())
            ->count();

        $subscriptionsCount = Subscription::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('vendor_id')
            ->count();

        return [
            'total_vendors' => $vendorCount,
            'linked_vendors' => $subscriptionsCount,
        ];
    }

    protected function vendorItems(Workspace $workspace): array
    {
        return Vendor::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['subscriptions' => fn ($query) => $query->select([
                'id',
                'vendor_id',
                'amount',
                'billing_cycle',
                'status',
                'next_renewal_date',
            ])])
            ->withCount('subscriptions')
            ->orderBy('name')
            ->get()
            ->map(function (Vendor $vendor) use ($workspace): array {
                $monthlyEquivalent = (float) $vendor->subscriptions->sum(fn (Subscription $subscription): float => $this->monthlyEquivalent($subscription));

                return [
                    'id' => $vendor->getKey(),
                    'name' => $vendor->name,
                    'contact_name' => $vendor->contact_name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                    'contract' => $vendor->contract,
                    'payment_terms' => $vendor->payment_terms,
                    'notes' => $vendor->notes,
                    'subscriptions_count' => $vendor->subscriptions_count ?? 0,
                    'monthly_equivalent_label' => $this->currency($monthlyEquivalent, $workspace),
                ];
            })
            ->values()
            ->all();
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'vendors' => Vendor::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Vendor $vendor): array => [
                    'id' => $vendor->getKey(),
                    'name' => $vendor->name,
                ])->values()->all(),
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'expired', 'label' => 'Expired'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'billingCycles' => [
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
            ],
            'renewalStates' => [
                ['value' => 'due_soon', 'label' => 'Due Soon'],
                ['value' => 'overdue', 'label' => 'Overdue'],
                ['value' => 'scheduled', 'label' => 'Scheduled'],
                ['value' => 'no_date', 'label' => 'No Renewal Date'],
            ],
            'expenseTransactions' => Transaction::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('type', 'expense')
                ->with('account:id,name')
                ->orderByDesc('date')
                ->limit(40)
                ->get(['id', 'account_id', 'category', 'amount', 'date'])
                ->map(fn (Transaction $transaction): array => [
                    'id' => $transaction->getKey(),
                    'name' => trim(sprintf(
                        '%s - %s - %s',
                        ucwords(str_replace(['-', '_'], ' ', $transaction->category ?: 'expense')),
                        number_format((float) $transaction->amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR'),
                        $transaction->date?->format('d M Y') ?? '-',
                    )),
                ])->values()->all(),
        ];
    }

    protected function transformSubscription(Subscription $subscription, Workspace $workspace): array
    {
        $effectiveStatus = $this->effectiveStatus($subscription);
        $renewalState = $this->renewalState($subscription);

        return [
            'id' => $subscription->getKey(),
            'vendor_id' => $subscription->vendor_id,
            'transaction_id' => $subscription->transaction_id,
            'name' => $subscription->name,
            'description' => $subscription->description,
            'amount' => (float) $subscription->amount,
            'amount_label' => $this->currency((float) $subscription->amount, $workspace),
            'billing_cycle' => $subscription->billing_cycle,
            'billing_cycle_label' => ucfirst($subscription->billing_cycle ?? 'monthly'),
            'status' => $subscription->status,
            'status_label' => ucfirst($subscription->status ?? 'active'),
            'effective_status' => $effectiveStatus,
            'effective_status_label' => ucfirst($effectiveStatus),
            'next_renewal_date' => $subscription->next_renewal_date?->toDateString(),
            'next_renewal_date_label' => $subscription->next_renewal_date?->format('d M Y'),
            'reminder_days_before' => (int) ($subscription->reminder_days_before ?? 7),
            'days_to_renewal' => $subscription->next_renewal_date
                ? now()->startOfDay()->diffInDays($subscription->next_renewal_date, false)
                : null,
            'renewal_state' => $renewalState,
            'renewal_state_label' => $this->renewalStateLabel($renewalState),
            'monthly_equivalent_label' => $this->currency($this->monthlyEquivalent($subscription), $workspace),
            'annualized_amount_label' => $this->currency($this->annualizedAmount($subscription), $workspace),
            'vendor' => $subscription->vendor ? [
                'id' => $subscription->vendor->getKey(),
                'name' => $subscription->vendor->name,
                'contact_name' => $subscription->vendor->contact_name,
                'email' => $subscription->vendor->email,
                'phone' => $subscription->vendor->phone,
                'contract' => $subscription->vendor->contract,
                'payment_terms' => $subscription->vendor->payment_terms,
                'notes' => $subscription->vendor->notes,
            ] : null,
            'transaction' => $subscription->transaction ? [
                'id' => $subscription->transaction->getKey(),
                'category' => $subscription->transaction->category,
                'category_label' => ucwords(str_replace(['-', '_'], ' ', $subscription->transaction->category ?: 'expense')),
                'amount' => (float) $subscription->transaction->amount,
                'amount_label' => $this->currency((float) $subscription->transaction->amount, $workspace),
                'date_label' => $subscription->transaction->date?->format('d M Y'),
                'account' => $subscription->transaction->account ? [
                    'id' => $subscription->transaction->account->getKey(),
                    'name' => $subscription->transaction->account->name,
                ] : null,
            ] : null,
        ];
    }

    protected function effectiveStatus(Subscription $subscription): string
    {
        if ($subscription->status === 'cancelled') {
            return 'cancelled';
        }

        if ($subscription->status === 'expired') {
            return 'expired';
        }

        if (
            $subscription->status === 'active'
            && $subscription->next_renewal_date !== null
            && $subscription->next_renewal_date->isPast()
            && ! $subscription->next_renewal_date->isToday()
        ) {
            return 'expired';
        }

        return 'active';
    }

    protected function renewalState(Subscription $subscription): string
    {
        if ($subscription->status === 'cancelled') {
            return 'cancelled';
        }

        if ($subscription->next_renewal_date === null) {
            return 'no_date';
        }

        $daysToRenewal = now()->startOfDay()->diffInDays($subscription->next_renewal_date, false);
        $reminderWindow = (int) ($subscription->reminder_days_before ?? 7);

        if ($daysToRenewal < 0) {
            return 'overdue';
        }

        if ($daysToRenewal <= $reminderWindow) {
            return 'due_soon';
        }

        return 'scheduled';
    }

    protected function renewalStateLabel(string $renewalState): string
    {
        return match ($renewalState) {
            'due_soon' => 'Due Soon',
            'overdue' => 'Overdue',
            'scheduled' => 'Scheduled',
            'cancelled' => 'Cancelled',
            default => 'No Renewal Date',
        };
    }

    protected function monthlyEquivalent(Subscription $subscription): float
    {
        $amount = (float) $subscription->amount;

        return $subscription->billing_cycle === 'yearly'
            ? round($amount / 12, 2)
            : $amount;
    }

    protected function annualizedAmount(Subscription $subscription): float
    {
        $amount = (float) $subscription->amount;

        return $subscription->billing_cycle === 'monthly'
            ? $amount * 12
            : $amount;
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
