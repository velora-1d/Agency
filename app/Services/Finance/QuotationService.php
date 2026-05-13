<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuotationService
{
    public function create(Workspace $workspace, array $data): Quotation
    {
        [$client, $lead] = $this->resolveClientAndLead($workspace, $data);

        $quotation = Quotation::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $client?->getKey(),
            'lead_id' => $lead?->getKey(),
            'number' => $this->generateQuotationNumber($workspace),
            'title' => $data['title'],
            'cover_letter' => $data['cover_letter'] ?? null,
            'scope_of_work' => $data['scope_of_work'] ?? null,
            'timeline' => $data['timeline'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? null,
            'status' => 'draft',
            'version' => 1,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_rate' => $data['tax_rate'] ?? 11,
            'dp_percentage' => $data['dp_percentage'] ?? 0,
            'valid_until' => $data['valid_until'] ?? now()->addDays(14)->toDateString(),
            'approval_token' => Str::random(40),
            'created_by' => Auth::id(),
        ]);

        $this->syncItems($quotation, $data['items']);
        $this->refreshPaymentSummary($quotation->fresh());
        $this->logActivity($workspace, $quotation, sprintf('Quotation %s berhasil dibuat.', $quotation->number), 'create', 'emerald');

        return $quotation->refresh()->load(['client', 'lead', 'items', 'creator']);
    }

    public function update(Workspace $workspace, Quotation $quotation, array $data): Quotation
    {
        abort_unless($quotation->workspace_id === $workspace->getKey(), 404);

        [$client, $lead] = $this->resolveClientAndLead($workspace, $data);

        $quotation->update([
            'client_id' => $client?->getKey(),
            'lead_id' => $lead?->getKey(),
            'title' => $data['title'],
            'cover_letter' => $data['cover_letter'] ?? null,
            'scope_of_work' => $data['scope_of_work'] ?? null,
            'timeline' => $data['timeline'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_rate' => $data['tax_rate'] ?? 11,
            'dp_percentage' => $data['dp_percentage'] ?? 0,
            'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
            'version' => (int) $quotation->version + 1,
            'status' => in_array($quotation->status, ['approved', 'rejected'], true) ? 'revised' : $quotation->status,
        ]);

        $this->syncItems($quotation, $data['items']);
        $this->refreshPaymentSummary($quotation->fresh());
        $this->logActivity($workspace, $quotation, sprintf('Quotation %s diperbarui.', $quotation->number), 'update', 'amber');

        return $quotation->refresh()->load(['client', 'lead', 'items', 'creator', 'approver']);
    }

    public function delete(Workspace $workspace, Quotation $quotation): void
    {
        abort_unless($quotation->workspace_id === $workspace->getKey(), 404);

        $number = $quotation->number;
        $quotation->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'quotation',
            'subject_type' => Quotation::class,
            'subject_id' => null,
            'description' => sprintf('Quotation %s dihapus.', $number),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateStatus(Workspace $workspace, Quotation $quotation, string $status): Quotation
    {
        abort_unless($quotation->workspace_id === $workspace->getKey(), 404);

        $attributes = ['status' => $status];

        if ($status === 'approved') {
            $attributes['approved_at'] = now();
            $attributes['approved_by'] = Auth::id();
        }

        if ($status === 'rejected') {
            $attributes['approved_at'] = null;
            $attributes['approved_by'] = Auth::id();
        }

        $quotation->update($attributes);

        $this->logActivity($workspace, $quotation, sprintf('Status quotation %s diubah ke %s.', $quotation->number, $status), 'status', 'sky');

        return $quotation->refresh();
    }

    public function send(Workspace $workspace, Quotation $quotation): Quotation
    {
        abort_unless($quotation->workspace_id === $workspace->getKey(), 404);

        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
            'approval_token' => $quotation->approval_token ?: Str::random(40),
        ]);

        $this->logActivity($workspace, $quotation, sprintf('Quotation %s dikirim ke client.', $quotation->number), 'send', 'blue');

        return $quotation->refresh();
    }

    public function approvePublic(Quotation $quotation, string $decision): Quotation
    {
        abort_if(blank($quotation->approval_token), 404);

        $quotation->update([
            'status' => $decision,
            'approved_at' => $decision === 'approved' ? now() : null,
            'approved_by' => null,
        ]);

        ActivityFeed::query()->create([
            'workspace_id' => $quotation->workspace_id,
            'user_id' => null,
            'type' => 'quotation',
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->getKey(),
            'description' => sprintf('Quotation %s diputuskan client sebagai %s.', $quotation->number, $decision),
            'metadata' => [
                'action' => 'approval',
                'icon' => 'BadgeCheck',
                'color' => $decision === 'approved' ? 'emerald' : 'rose',
            ],
            'created_at' => now(),
        ]);

        return $quotation->refresh();
    }

    public function convertToInvoiceAndProject(Workspace $workspace, Quotation $quotation, bool $createProject = true): array
    {
        abort_unless($quotation->workspace_id === $workspace->getKey(), 404);
        abort_if($quotation->status !== 'approved', 422, 'Quotation must be approved before conversion.');

        $client = $this->resolveConversionClient($workspace, $quotation);
        $existingInvoice = $quotation->invoices()->with('project')->latest()->first();
        $project = $existingInvoice?->project;

        if ($project === null && $createProject) {
            $project = Project::query()->create([
                'workspace_id' => $workspace->getKey(),
                'client_id' => $client?->getKey(),
                'name' => $quotation->title,
                'description' => trim(implode("\n\n", array_filter([
                    $quotation->scope_of_work,
                    $quotation->timeline,
                ]))),
                'status' => 'planning',
                'budget' => $quotation->total,
                'actual_cost' => 0,
                'created_by' => Auth::id(),
            ]);
        }

        $invoice = $existingInvoice;

        if ($invoice === null) {
            $invoice = Invoice::query()->create([
                'workspace_id' => $workspace->getKey(),
                'client_id' => $client?->getKey(),
                'project_id' => $project?->getKey(),
                'quotation_id' => $quotation->getKey(),
                'number' => $this->generateInvoiceNumber($workspace),
                'type' => 'invoice',
                'status' => 'draft',
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_rate' => $quotation->tax_rate,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'paid_amount' => 0,
                'currency' => $workspace->currency ?? 'IDR',
                'due_date' => $quotation->valid_until ?? now()->addDays(14)->toDateString(),
                'notes' => $quotation->scope_of_work,
                'created_by' => Auth::id(),
            ]);

            foreach ($quotation->items as $index => $item) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->getKey(),
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'order_index' => $index,
                ]);
            }

            $invoice->recalculateTotals();
        }

        $this->logActivity($workspace, $quotation, sprintf('Quotation %s dikonversi menjadi invoice.', $quotation->number), 'convert', 'emerald');

        return [
            'project' => $project,
            'invoice' => $invoice->refresh(),
        ];
    }

    protected function resolveClientAndLead(Workspace $workspace, array $data): array
    {
        $client = null;
        $lead = null;

        if (! empty($data['client_id'])) {
            $client = Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->findOrFail($data['client_id']);
        }

        if (! empty($data['lead_id'])) {
            $lead = Lead::query()
                ->where('workspace_id', $workspace->getKey())
                ->findOrFail($data['lead_id']);
        }

        return [$client, $lead];
    }

    protected function resolveConversionClient(Workspace $workspace, Quotation $quotation): ?Client
    {
        if ($quotation->client_id) {
            return Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($quotation->client_id);
        }

        if ($quotation->lead?->converted_to_client_id) {
            return Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($quotation->lead->converted_to_client_id);
        }

        return null;
    }

    protected function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach (collect($items)->values() as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);
            $subtotal = max(0, ($quantity * $unitPrice) - $discount);

            $quotation->items()->create([
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'category' => $item['category'] ?? null,
                'quantity' => $quantity,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'order_index' => $index,
            ]);
        }
    }

    protected function refreshPaymentSummary(Quotation $quotation): void
    {
        $quotation->recalculateTotals();

        $dpAmount = ((float) $quotation->total) * ((float) ($quotation->dp_percentage ?? 0) / 100);

        $quotation->updateQuietly([
            'dp_amount' => $dpAmount,
        ]);
    }

    protected function generateQuotationNumber(Workspace $workspace): string
    {
        $prefix = 'QUO-' . now()->format('Ym');
        $count = Quotation::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('number', 'like', "{$prefix}-%")
            ->count() + 1;

        return sprintf('%s-%04d', $prefix, $count);
    }

    protected function generateInvoiceNumber(Workspace $workspace): string
    {
        $prefix = 'INV-' . now()->format('Ym');
        $count = Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('number', 'like', "{$prefix}-%")
            ->count() + 1;

        return sprintf('%s-%04d', $prefix, $count);
    }

    protected function logActivity(
        Workspace $workspace,
        Quotation $quotation,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'quotation',
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FilePlus',
                    'update' => 'Pencil',
                    'send' => 'Send',
                    'status' => 'BadgeCheck',
                    'convert' => 'Receipt',
                    default => 'FileText',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
