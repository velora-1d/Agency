<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'client_id',
        'folder_id',
        'name',
        'original_name',
        'path',
        'mime_type',
        'size_bytes',
        'version',
        'parent_file_id',
        'approval_status',
        'approved_by',
        'share_token',
        'share_expires_at',
        'uploaded_by',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'version' => 'integer',
        'share_expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function parentFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_file_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(File::class, 'parent_file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
