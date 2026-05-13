<?php

namespace App\Modules\Finance\Quotations\Queries;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class QuotationIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $quotations = $this->quotationQuery($workspace, $filters)->get();

        return [
            'quotations' => [
                'summary' => $this->buildSummary($quotations, $workspace),
                'items' => $quotations->map(fn (Quotation $quotation): array => $this->transformQuotation($quotation, $workspace))->values()->all(),
                'selected_id' => $filters['quotation'],
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
            'lead' => $normalize($input['lead'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'quotation' => $normalize($input['quotation'] ?? null),
        ];
    }

    protected function quotationQuery(Workspace $workspace, array $filters): Builder
    {
        return Quotation::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'client:id,company_name,pic_name',
                'lead:id,name,company',
                'items',
                'invoices:id,quotation_id,project_id,number,status,total',
                'invoices.project:id,name',
                'creator:id,name',
                'approver:id,name',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%")
                        ->orWhere('cover_letter', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('lead', fn (Builder $leadQuery) => $leadQuery->where('company', 'like', "%{$search}%"));
                });
            })
            ->when($filters['client'], fn (Builder $query, string $clientId) => $query->where('client_id', $clientId))
            ->when($filters['lead'], fn (Builder $query, string $leadId) => $query->where('lead_id', $leadId))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');
    }

    protected function buildSummary(Collection $quotations, Workspace $workspace): array
    {
        return [
            'total_quotations' => $quotations->count(),
            'draft_quotations' => $quotations->where('status', 'draft')->count(),
            'sent_quotations' => $quotations->where('status', 'sent')->count(),
            'approved_quotations' => $quotations->where('status', 'approved')->count(),
            'pending_value' => (float) $quotations->whereIn('status', ['sent', 'revised'])->sum('total'),
            'pending_value_label' => $this->currency((float) $quotations->whereIn('status', ['sent', 'revised'])->sum('total'), $workspace),
            'approved_value_label' => $this->currency((float) $quotations->where('status', 'approved')->sum('total'), $workspace),
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
            'leads' => Lead::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company')
                ->get(['id', 'company', 'name'])
                ->map(fn (Lead $lead): array => [
                    'id' => $lead->getKey(),
                    'name' => trim(($lead->company ?: $lead->name) . ($lead->company && $lead->name ? ' / ' . $lead->name : '')),
                ])->values()->all(),
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
                ['value' => 'revised', 'label' => 'Revised'],
            ],
            'itemCategories' => [
                ['value' => 'development', 'label' => 'Development'],
                ['value' => 'design', 'label' => 'Design'],
                ['value' => 'server_hosting', 'label' => 'Server / Hosting'],
                ['value' => 'domain', 'label' => 'Domain'],
                ['value' => 'maintenance', 'label' => 'Maintenance'],
                ['value' => 'ssl', 'label' => 'SSL'],
                ['value' => 'revisi_tambahan', 'label' => 'Revisi Tambahan'],
                ['value' => 'konsultasi', 'label' => 'Konsultasi'],
                ['value' => 'custom', 'label' => 'Custom Item'],
            ],
        ];
    }

    protected function transformQuotation(Quotation $quotation, Workspace $workspace): array
    {
        $dpAmount = (float) ($quotation->dp_amount ?? 0);
        $remainingAmount = max(0, (float) $quotation->total - $dpAmount);
        $approvalUrl = $quotation->approval_token ? route('quotations.public.show', ['token' => $quotation->approval_token]) : null;
        $invoice = $quotation->invoices->first();

        return [
            'id' => $quotation->getKey(),
            'client_id' => $quotation->client_id,
            'lead_id' => $quotation->lead_id,
            'number' => $quotation->number,
            'title' => $quotation->title,
            'cover_letter' => $quotation->cover_letter,
            'scope_of_work' => $quotation->scope_of_work,
            'timeline' => $quotation->timeline,
            'terms_conditions' => $quotation->terms_conditions,
            'status' => $quotation->status,
            'status_label' => ucfirst($quotation->status),
            'version' => (int) $quotation->version,
            'subtotal' => (float) $quotation->subtotal,
            'subtotal_label' => $this->currency((float) $quotation->subtotal, $workspace),
            'discount_amount' => (float) $quotation->discount_amount,
            'discount_amount_label' => $this->currency((float) $quotation->discount_amount, $workspace),
            'tax_rate' => (float) $quotation->tax_rate,
            'tax_rate_label' => number_format((float) $quotation->tax_rate, 0) . '%',
            'tax_amount' => (float) $quotation->tax_amount,
            'tax_amount_label' => $this->currency((float) $quotation->tax_amount, $workspace),
            'total' => (float) $quotation->total,
            'total_label' => $this->currency((float) $quotation->total, $workspace),
            'dp_percentage' => (float) ($quotation->dp_percentage ?? 0),
            'dp_percentage_label' => number_format((float) ($quotation->dp_percentage ?? 0), 0) . '%',
            'dp_amount' => $dpAmount,
            'dp_amount_label' => $this->currency($dpAmount, $workspace),
            'remaining_amount' => $remainingAmount,
            'remaining_amount_label' => $this->currency($remainingAmount, $workspace),
            'valid_until' => $quotation->valid_until?->toDateString(),
            'valid_until_label' => $quotation->valid_until?->format('d M Y'),
            'sent_at_label' => $quotation->sent_at?->format('d M Y H:i'),
            'approved_at_label' => $quotation->approved_at?->format('d M Y H:i'),
            'approval_url' => $approvalUrl,
            'approval_token' => $quotation->approval_token,
            'client' => $quotation->client ? [
                'id' => $quotation->client->getKey(),
                'name' => $quotation->client->company_name,
                'pic_name' => $quotation->client->pic_name,
            ] : null,
            'lead' => $quotation->lead ? [
                'id' => $quotation->lead->getKey(),
                'name' => $quotation->lead->name,
                'company' => $quotation->lead->company,
            ] : null,
            'creator' => $quotation->creator ? [
                'id' => $quotation->creator->getKey(),
                'name' => $quotation->creator->name,
            ] : null,
            'approver' => $quotation->approver ? [
                'id' => $quotation->approver->getKey(),
                'name' => $quotation->approver->name,
            ] : null,
            'invoice' => $invoice ? [
                'id' => $invoice->getKey(),
                'number' => $invoice->number,
                'status' => $invoice->status,
                'project' => $invoice->project ? [
                    'id' => $invoice->project->getKey(),
                    'name' => $invoice->project->name,
                ] : null,
            ] : null,
            'items' => $quotation->items->sortBy('order_index')->values()->map(fn ($item): array => [
                'id' => $item->getKey(),
                'name' => $item->name,
                'description' => $item->description,
                'category' => $item->category,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'unit_price_label' => $this->currency((float) $item->unit_price, $workspace),
                'discount_amount' => (float) $item->discount_amount,
                'discount_amount_label' => $this->currency((float) $item->discount_amount, $workspace),
                'subtotal' => (float) $item->subtotal,
                'subtotal_label' => $this->currency((float) $item->subtotal, $workspace),
            ])->all(),
            'counts' => [
                'items' => $quotation->items->count(),
            ],
        ];
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
