<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'title',
        'caption',
        'hashtags',
        'platforms',
        'media_files',
        'status',
        'scheduled_at',
        'posted_at',
        'analytics',
        'created_by',
    ];

    protected $casts = [
        'platforms' => 'array',
        'media_files' => 'array',
        'analytics' => 'array',
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
