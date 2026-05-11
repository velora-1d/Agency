<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'name',
        'type',
        'status',
        'budget',
        'spend',
        'start_date',
        'end_date',
        'external_ids',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spend' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'external_ids' => 'array',
    ];
}
