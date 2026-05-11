<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'project_id',
        'quotation_id',
        'contract_id',
        'number',
        'type',
        'status',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_amount',
        'currency',
        'due_date',
        'is_recurring',
        'recurrence_rule',
        'payment_method',
        'pakasir_order_id',
        'pakasir_payment_url',
        'internal_approved_at',
        'internal_approved_by',
        'sent_at',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'is_recurring' => 'boolean',
        'internal_approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'updated' => \App\Events\InvoicePaid::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
