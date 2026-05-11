<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'pipeline_id',
        'stage_id',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'source',
        'estimated_value',
        'priority',
        'assigned_to',
        'ai_score',
        'notes',
        'converted_at',
        'converted_to_client_id',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'ai_score' => 'integer',
        'converted_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\LeadCreated::class,
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }
}
