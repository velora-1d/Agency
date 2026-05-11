<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFinanceSplit extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'template_name',
        'kas_kantor_percentage',
        'kas_kantor_amount',
        'payment_trigger',
        'payment_trigger_custom',
        'total_project_value',
        'total_operational_cost',
        'total_kas_kantor',
        'total_team_fee',
    ];

    protected $casts = [
        'kas_kantor_percentage' => 'decimal:2',
        'kas_kantor_amount' => 'decimal:2',
        'total_project_value' => 'decimal:2',
        'total_operational_cost' => 'decimal:2',
        'total_kas_kantor' => 'decimal:2',
        'total_team_fee' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectFinanceSplitItem::class, 'split_id');
    }
}
