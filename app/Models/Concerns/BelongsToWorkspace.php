<?php

namespace App\Models\Concerns;

use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    protected static function bootBelongsToWorkspace(): void
    {
        static::creating(function ($model) {
            if (! empty($model->workspace_id)) {
                return;
            }

            $workspaceId = WorkspaceContext::id();

            if (filled($workspaceId)) {
                $model->workspace_id = $workspaceId;
            }
        });

        static::addGlobalScope('workspace', function (Builder $builder) {
            $workspaceId = WorkspaceContext::id();

            if (filled($workspaceId)) {
                $builder->where('workspace_id', $workspaceId);
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
