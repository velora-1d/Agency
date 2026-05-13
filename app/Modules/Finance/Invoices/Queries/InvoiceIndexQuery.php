<?php

namespace App\Modules\Finance\Invoices\Queries;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InvoiceIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $invoices = $this->invoiceQuery($workspace, $filters)->get();

        return [
            'invoices' => [
                'summary' => $this->buildSummary($invoices, $workspace),
                'items' => $invoices->map(fn (Invoice $invoice): array => $this->transformInvoice($invoice, $workspace))->values()->all(),
                'selected_id' => $filters['invoice'],
                'brand' => [
                    'workspace_name' => $workspace->name,
                    'workspace_slug' => $workspace->slug,
                    'currency' => $workspace->currency ?? 'IDR',
                    'primary_color' => $workspace->primary_color,
                ],
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
            'client' => $normalize($input['client'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'quotation' => $normalize($input['quotation'] ?? null),
            'contract' => $normalize($input['contract'] ?? null),
            'type' => $normalize($input['type'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'currency' => $normalize($input['currency'] ?? null),
            'payment_method' => $normalize($input['payment_method'] ?? null),
            'invoice' => $normalize($input['invoice'] ?? null),
        ];
    }

    protected function invoiceQuery(Workspace $workspace, array $filters): Builder
    {
        return Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'client:id,company_name,pic_name',
                'project:id,name',
                'quotation:id,number,title,status',
                'contract:id,title,status',
                'creator:id,name',
                'internalApprover:id,name',
                'items',
                'payments' => fn ($query) => $query
                    ->with('verifier:id,name')
                    ->latest('created_at'),
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('quotation', fn (Builder $quotationQuery) => $quotationQuery->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('contract', fn (Builder $contractQuery) => $contractQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->when($filters['client'], fn (Builder $query, string $clientId) => $query->where('client_id', $clientId))
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['quotation'], fn (Builder $query, string $quotationId) => $query->where('quotation_id', $quotationId))
            ->when($filters['contract'], fn (Builder $query, string $contractId) => $query->where('contract_id', $contractId))
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['currency'], fn (Builder $query, string $currency) => $query->where('currency', strtoupper($currency)))
            ->when($filters['payment_method'], fn (Builder $query, string $method) => $query->where('payment_method', $method))
            ->when($filters['status'], function (Builder $query, string $status): void {
                if ($status === 'overdue') {
                    $query->where(function (Builder $builder): void {
                        $builder
                            ->where('status', 'overdue')
                            ->orWhere(function (Builder $overdueQuery): void {
                                $overdueQuery
                                    ->whereIn('status', ['draft', 'sent', 'partial'])
                                    ->whereDate('due_date', '<', now()->toDateString());
                            });
                    });

                    return;
                }

                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN status = 'paid' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('due_date')
            ->orderByDesc('updated_at');
    }

    protected function buildSummary(Collection $invoices, Workspace $workspace): array
    {
        $enriched = $invoices->map(fn (Invoice $invoice): array => [
            'effective_status' => $this->effectiveStatus($invoice),
            'outstanding' => $this->outstandingAmount($invoice),
            'paid' => (float) $invoice->paid_amount,
        ]);

        $outstandingTotal = (float) $enriched->sum('outstanding');
        $paidTotal = (float) $enriched->sum('paid');

        return [
            'total_invoices' => $invoices->count(),
            'draft_invoices' => $enriched->where('effective_status', 'draft')->count(),
            'sent_invoices' => $enriched->where('effective_status', 'sent')->count(),
            'partial_invoices' => $enriched->where('effective_status', 'partial')->count(),
            'paid_invoices' => $enriched->where('effective_status', 'paid')->count(),
            'overdue_invoices' => $enriched->where('effective_status', 'overdue')->count(),
            'proforma_invoices' => $invoices->where('type', 'proforma')->count(),
            'credit_notes' => $invoices->where('type', 'credit_note')->count(),
            'recurring_invoices' => $invoices->where('is_recurring', true)->count(),
            'pending_internal_approval' => $invoices
                ->filter(fn (Invoice $invoice): bool => $invoice->internal_approved_at === null && $invoice->status === 'draft')
                ->count(),
            'outstanding_total' => $outstandingTotal,
            'outstanding_total_label' => $this->currency($outstandingTotal, $workspace->currency ?? 'IDR'),
            'paid_total' => $paidTotal,
            'paid_total_label' => $this->currency($paidTotal, $workspace->currency ?? 'IDR'),
        ];
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'clients' => Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Client $client): array => [
                    'id' => $client->getKey(),
                    'name' => $client->company_name,
                ])->values()->all(),
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])->values()->all(),
            'quotations' => Quotation::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderByDesc('created_at')
                ->get(['id', 'number', 'title'])
                ->map(fn (Quotation $quotation): array => [
                    'id' => $quotation->getKey(),
                    'name' => trim($quotation->number . ' - ' . $quotation->title),
                ])->values()->all(),
            'contracts' => Contract::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderByDesc('created_at')
                ->get(['id', 'title'])
                ->map(fn (Contract $contract): array => [
                    'id' => $contract->getKey(),
                    'name' => $contract->title,
                ])->values()->all(),
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draf'],
                ['value' => 'sent', 'label' => 'Terkirim'],
                ['value' => 'partial', 'label' => 'Dicicil'],
                ['value' => 'paid', 'label' => 'Lunas'],
                ['value' => 'overdue', 'label' => 'Telat Bayar'],
            ],
            'types' => [
                ['value' => 'invoice', 'label' => 'Tagihan'],
                ['value' => 'proforma', 'label' => 'Proforma'],
                ['value' => 'credit_note', 'label' => 'Nota Kredit'],
            ],
            'currencies' => collect([$workspace->currency ?? 'IDR', 'USD', 'SGD', 'MYR', 'EUR'])
                ->filter()
                ->unique()
                ->values()
                ->map(fn (string $currency): array => [
                    'value' => strtoupper($currency),
                    'label' => strtoupper($currency),
                ])->all(),
            'paymentMethods' => $this->paymentMethodOptions($workspace),
            'recurrenceRules' => [
                ['value' => 'weekly', 'label' => 'Mingguan'],
                ['value' => 'monthly', 'label' => 'Bulanan'],
                ['value' => 'quarterly', 'label' => 'Kuartal'],
                ['value' => 'yearly', 'label' => 'Tahunan'],
            ],
        ];
    }

    protected function transformInvoice(Invoice $invoice, Workspace $workspace): array
    {
        $effectiveStatus = $this->effectiveStatus($invoice);
        $outstanding = $this->outstandingAmount($invoice);
        $currency = $invoice->currency ?: ($workspace->currency ?? 'IDR');

        return [
            'id' => $invoice->getKey(),
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'quotation_id' => $invoice->quotation_id,
            'contract_id' => $invoice->contract_id,
            'number' => $invoice->number,
            'type' => $invoice->type,
            'type_label' => $this->typeLabel($invoice->type),
            'status' => $invoice->status,
            'status_label' => $this->statusLabel($invoice->status),
            'effective_status' => $effectiveStatus,
            'effective_status_label' => $this->statusLabel($effectiveStatus),
            'subtotal' => (float) $invoice->subtotal,
            'subtotal_label' => $this->currency((float) $invoice->subtotal, $currency),
            'discount_amount' => (float) $invoice->discount_amount,
            'discount_amount_label' => $this->currency((float) $invoice->discount_amount, $currency),
            'tax_rate' => (float) $invoice->tax_rate,
            'tax_rate_label' => number_format((float) $invoice->tax_rate, 0) . '%',
            'tax_amount' => (float) $invoice->tax_amount,
            'tax_amount_label' => $this->currency((float) $invoice->tax_amount, $currency),
            'total' => (float) $invoice->total,
            'total_label' => $this->currency((float) $invoice->total, $currency),
            'paid_amount' => (float) $invoice->paid_amount,
            'paid_amount_label' => $this->currency((float) $invoice->paid_amount, $currency),
            'outstanding_amount' => $outstanding,
            'outstanding_amount_label' => $this->currency($outstanding, $currency),
            'currency' => $currency,
            'due_date' => $invoice->due_date?->toDateString(),
            'due_date_label' => $invoice->due_date?->format('d M Y'),
            'days_to_due' => $invoice->due_date ? now()->startOfDay()->diffInDays($invoice->due_date, false) : null,
            'reminder_state' => $this->reminderState($invoice),
            'is_recurring' => (bool) $invoice->is_recurring,
            'recurrence_rule' => $invoice->recurrence_rule,
            'recurrence_rule_label' => $invoice->recurrence_rule ? ucfirst($invoice->recurrence_rule) : 'One-time',
            'payment_method' => $invoice->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($invoice->payment_method),
            'pakasir_order_id' => $invoice->pakasir_order_id,
            'pakasir_payment_url' => $invoice->pakasir_payment_url,
            'internal_approved_at_label' => $invoice->internal_approved_at?->format('d M Y H:i'),
            'sent_at_label' => $invoice->sent_at?->format('d M Y H:i'),
            'paid_at_label' => $invoice->paid_at?->format('d M Y H:i'),
            'notes' => $invoice->notes,
            'can_send' => $invoice->internal_approved_at !== null && in_array($invoice->status, ['draft', 'overdue'], true),
            'can_record_payment' => $invoice->type !== 'credit_note' && $effectiveStatus !== 'paid',
            'payment_progress' => (float) ($invoice->total > 0 ? min(100, round(((float) $invoice->paid_amount / (float) $invoice->total) * 100, 2)) : 0),
            'client' => $invoice->client ? [
                'id' => $invoice->client->getKey(),
                'name' => $invoice->client->company_name,
                'pic_name' => $invoice->client->pic_name,
            ] : null,
            'project' => $invoice->project ? [
                'id' => $invoice->project->getKey(),
                'name' => $invoice->project->name,
            ] : null,
            'quotation' => $invoice->quotation ? [
                'id' => $invoice->quotation->getKey(),
                'number' => $invoice->quotation->number,
                'title' => $invoice->quotation->title,
                'status' => $invoice->quotation->status,
            ] : null,
            'contract' => $invoice->contract ? [
                'id' => $invoice->contract->getKey(),
                'title' => $invoice->contract->title,
                'status' => $invoice->contract->status,
            ] : null,
            'creator' => $invoice->creator ? [
                'id' => $invoice->creator->getKey(),
                'name' => $invoice->creator->name,
            ] : null,
            'internal_approver' => $invoice->internalApprover ? [
                'id' => $invoice->internalApprover->getKey(),
                'name' => $invoice->internalApprover->name,
            ] : null,
            'payments' => $invoice->payments->map(fn (Payment $payment): array => [
                'id' => $payment->getKey(),
                'amount' => (float) $payment->amount,
                'amount_label' => $this->currency((float) $payment->amount, $currency),
                'method' => $payment->method,
                'method_label' => $this->paymentMethodLabel($payment->method),
                'status' => $payment->status,
                'status_label' => ucfirst($payment->status),
                'paid_at_label' => $payment->paid_at?->format('d M Y H:i'),
                'verified_at_label' => $payment->verified_at?->format('d M Y H:i'),
                'notes' => $payment->notes,
                'verifier' => $payment->verifier ? [
                    'id' => $payment->verifier->getKey(),
                    'name' => $payment->verifier->name,
                ] : null,
            ])->values()->all(),
            'items' => $invoice->items->sortBy('order_index')->values()->map(fn ($item): array => [
                'id' => $item->getKey(),
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'unit_price_label' => $this->currency((float) $item->unit_price, $currency),
                'subtotal' => (float) $item->subtotal,
                'subtotal_label' => $this->currency((float) $item->subtotal, $currency),
            ])->all(),
            'counts' => [
                'items' => $invoice->items->count(),
                'payments' => $invoice->payments->count(),
            ],
        ];
    }

    protected function effectiveStatus(Invoice $invoice): string
    {
        if ($invoice->status === 'paid') {
            return 'paid';
        }

        if ($invoice->status === 'overdue') {
            return 'overdue';
        }

        if (
            $invoice->due_date !== null
            && $invoice->due_date->isPast()
            && ! $invoice->due_date->isToday()
            && in_array($invoice->status, ['draft', 'sent', 'partial'], true)
        ) {
            return 'overdue';
        }

        return $invoice->status;
    }

    protected function outstandingAmount(Invoice $invoice): float
    {
        return max(0, (float) $invoice->total - (float) $invoice->paid_amount);
    }

    protected function reminderState(Invoice $invoice): string
    {
        if ($this->effectiveStatus($invoice) === 'paid') {
            return 'completed';
        }

        if ($invoice->due_date === null) {
            return 'unscheduled';
        }

        $days = now()->startOfDay()->diffInDays($invoice->due_date, false);

        if ($days < 0) {
            return 'urgent';
        }

        if ($days <= 3) {
            return 'ready';
        }

        return 'scheduled';
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Draf',
            'sent' => 'Terkirim',
            'partial' => 'Dicicil',
            'paid' => 'Lunas',
            'overdue' => 'Telat Bayar',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function typeLabel(string $type): string
    {
        return match ($type) {
            'proforma' => 'Proforma',
            'credit_note' => 'Nota Kredit',
            default => 'Tagihan',
        };
    }

    protected function paymentMethodLabel(?string $method): string
    {
        if ($method === null || $method === '') {
            return 'Belum Diatur';
        }

        $map = [
            'manual_transfer' => 'Transfer Manual',
            'pakasir_qris' => 'Pakasir QRIS',
            'pakasir_bni_va' => 'Pakasir BNI VA',
            'pakasir_bri_va' => 'Pakasir BRI VA',
            'pakasir_bnc_va' => 'Pakasir BNC VA',
            'pakasir_cimb_niaga_va' => 'Pakasir CIMB Niaga VA',
            'pakasir_maybank_va' => 'Pakasir Maybank VA',
            'pakasir_permata_va' => 'Pakasir Permata VA',
            'pakasir_atm_bersama_va' => 'Pakasir ATM Bersama VA',
            'pakasir_artha_graha_va' => 'Pakasir Artha Graha VA',
            'pakasir_sampoerna_va' => 'Pakasir Sampoerna VA',
        ];

        if (isset($map[$method])) {
            return $map[$method];
        }

        if (str_starts_with($method, 'pakasir_')) {
            return 'Pakasir ' . str($method)->after('pakasir_')->replace('_', ' ')->title();
        }

        return str($method)->replace('_', ' ')->title()->toString();
    }

    protected function currency(float $amount, string $currency): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . strtoupper($currency);
    }

    protected function pakasirPaymentMethods(): array
    {
        return collect(config('services.pakasir.payment_methods', []))
            ->filter()
            ->unique()
            ->map(fn (string $method): array => [
                'value' => $method,
                'label' => $this->paymentMethodLabel($method),
            ])
            ->values()
            ->all();
    }

    protected function paymentMethodOptions(Workspace $workspace): array
    {
        return collect([
            ['value' => 'manual_transfer', 'label' => 'Transfer Manual'],
            ...$this->pakasirPaymentMethods(),
            ...$this->dynamicPaymentMethods($workspace),
        ])
            ->unique('value')
            ->values()
            ->all();
    }

    protected function dynamicPaymentMethods(Workspace $workspace): array
    {
        $invoiceMethods = Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->all();

        $paymentMethods = Payment::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('method')
            ->distinct()
            ->pluck('method')
            ->all();

        return collect(array_merge($invoiceMethods, $paymentMethods))
            ->filter()
            ->unique()
            ->reject(fn (string $method) => $method === 'manual_transfer')
            ->map(fn (string $method): array => [
                'value' => $method,
                'label' => $this->paymentMethodLabel($method),
            ])
            ->values()
            ->all();
    }
}
