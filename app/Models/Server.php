<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Server extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'name',
        'ip_address',
        'provider',
        'type',
        'location',
        'specs',
        'os',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'specs' => 'array',
        'last_checked_at' => 'datetime',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function credentials(): MorphMany
    {
        return $this->morphMany(ServiceCredential::class, 'credentialable');
    }
}
