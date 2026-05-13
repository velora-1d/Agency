<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRunLog extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'automation_workflow_id',
        'trigger_event',
        'status',
        'attempt',
        'message',
        'payload',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'attempt' => 'integer',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class, 'automation_workflow_id');
    }
}
