<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'description',
        'is_default',
        'parent_role_id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_role_id');
    }

    public function childRoles(): HasMany
    {
        return $this->hasMany(self::class, 'parent_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}
