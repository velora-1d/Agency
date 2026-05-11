<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;
    use HasUuids;
    use Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'primary_color',
        'timezone',
        'currency',
        'language',
        'custom_domain',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'wa_api_key',
        'wa_phone_number',
        'n8n_webhook_url',
        'working_hours_start',
        'working_hours_end',
        'storage_quota_gb',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'smtp_port' => 'integer',
            'storage_quota_gb' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_users')
            ->withPivot(['id', 'role_id', 'is_owner', 'joined_at', 'expires_at']);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceUser::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
