<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaSession extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'instance_name',
        'status',
        'phone_number',
        'apikey',
        'config',
        'last_connected_at',
    ];

    protected $casts = [
        'config' => 'array',
        'last_connected_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'session_id');
    }
}
