<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'quotation_id',
        'name',
        'description',
        'category',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'subtotal',
        'order_index',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    protected static function booted()
    {
        static::saved(fn ($item) => $item->quotation->recalculateTotals());
        static::deleted(fn ($item) => $item->quotation->recalculateTotals());
    }
}
