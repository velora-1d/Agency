<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'folder_id',
        'title',
        'content',
        'type',
        'is_private',
        'version',
        'created_by',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'version' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(NoteFolder::class, 'folder_id');
    }
}
