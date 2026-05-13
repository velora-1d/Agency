<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'lead_id',
        'number',
        'title',
        'cover_letter',
        'scope_of_work',
        'timeline',
        'terms_conditions',
        'status',
        'version',
        'parent_quotation_id',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'dp_percentage',
        'dp_amount',
        'valid_until',
        'approved_at',
        'approval_token',
        'sent_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'dp_percentage' => 'decimal:2',
        'dp_amount' => 'decimal:2',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $taxAmount = ($subtotal - $this->discount_amount) * ($this->tax_rate / 100);
        $total = $subtotal - $this->discount_amount + $taxAmount;

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }
}
