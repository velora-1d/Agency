<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityFeed extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $table = 'activity_feed';
    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'type',
        'subject_type',
        'subject_id',
        'description',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ActivityComment::class, 'activity_id');
    }
}
