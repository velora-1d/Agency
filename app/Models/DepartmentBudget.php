<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentBudget extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'department',
        'period_label',
        'limit_amount',
        'notes',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
    ];
}
