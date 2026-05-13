<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'vendor_id',
        'transaction_id',
        'name',
        'description',
        'amount',
        'billing_cycle',
        'status',
        'next_renewal_date',
        'reminder_days_before',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_renewal_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
