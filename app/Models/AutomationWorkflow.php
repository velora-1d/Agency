<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationWorkflow extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'name',
        'trigger_event',
        'n8n_workflow_id',
        'n8n_webhook_url',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function runLogs(): HasMany
    {
        return $this->hasMany(AutomationRunLog::class, 'automation_workflow_id');
    }
}
