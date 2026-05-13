<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\Finance\QuotationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationPublicController extends Controller
{
    public function show(string $token): Response
    {
        $quotation = Quotation::query()
            ->where('approval_token', $token)
            ->with([
                'client:id,company_name,pic_name',
                'lead:id,name,company',
                'workspace:id,name,logo',
                'items',
            ])
            ->firstOrFail();

        abort_if($quotation->status === 'approved', 403, 'Quotation ini sudah di-approve.');

        return Inertia::render('Finance/Quotations/PublicApprove', [
            'quotation' => [
                'id' => $quotation->getKey(),
                'title' => $quotation->title,
                'number' => $quotation->number,
                'cover_letter' => $quotation->cover_letter,
                'scope_of_work' => $quotation->scope_of_work,
                'timeline' => $quotation->timeline,
                'terms_conditions' => $quotation->terms_conditions,
                'status' => $quotation->status,
                'valid_until_label' => $quotation->valid_until?->format('d M Y'),
                'subtotal_label' => $this->currency((float) $quotation->subtotal, $quotation->workspace?->currency),
                'discount_amount_label' => $this->currency((float) $quotation->discount_amount, $quotation->workspace?->currency),
                'tax_amount_label' => $this->currency((float) $quotation->tax_amount, $quotation->workspace?->currency),
                'total_label' => $this->currency((float) $quotation->total, $quotation->workspace?->currency),
                'dp_amount_label' => $this->currency((float) ($quotation->dp_amount ?? 0), $quotation->workspace?->currency),
                'remaining_amount_label' => $this->currency(max(0, (float) $quotation->total - (float) ($quotation->dp_amount ?? 0)), $quotation->workspace?->currency),
                'client_name' => $quotation->client?->company_name ?: $quotation->lead?->company,
                'pic_name' => $quotation->client?->pic_name ?: $quotation->lead?->name,
                'workspace_name' => $quotation->workspace?->name,
                'workspace_logo' => $quotation->workspace?->logo,
                'items' => $quotation->items->map(fn ($item): array => [
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => $item->category,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit,
                    'unit_price_label' => $this->currency((float) $item->unit_price, $quotation->workspace?->currency),
                    'discount_amount_label' => $this->currency((float) $item->discount_amount, $quotation->workspace?->currency),
                    'subtotal_label' => $this->currency((float) $item->subtotal, $quotation->workspace?->currency),
                ])->values()->all(),
            ],
            'token' => $token,
        ]);
    }

    public function decide(Request $request, string $token, QuotationService $service): Response
    {
        $quotation = Quotation::query()
            ->where('approval_token', $token)
            ->firstOrFail();

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $service->approvePublic($quotation, $validated['decision']);

        return Inertia::render('Finance/Quotations/PublicDecision', [
            'quotation_title' => $quotation->title,
            'decision' => $validated['decision'],
        ]);
    }

    protected function currency(float $amount, ?string $currency): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($currency ?: 'IDR');
    }
}
