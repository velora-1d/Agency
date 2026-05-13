<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinanceSplitItem extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'split_id',
        'type',
        'component_type',
        'label',
        'user_id',
        'calculation_type',
        'percentage',
        'flat_amount',
        'final_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'flat_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function split(): BelongsTo
    {
        return $this->belongsTo(ProjectFinanceSplit::class, 'split_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
