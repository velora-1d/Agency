<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;

#[Fillable([
    'name',
    'email',
    'google_id',
    'password',
    'pin',
    'phone',
    'avatar',
    'is_active',
    'email_verified_at',
    'two_factor_secret',
    'two_factor_enabled',
    'app_authentication_recovery_codes',
    'last_login_at',
    'last_login_ip',
])]
#[Hidden([
    'password',
    'pin',
    'remember_token',
    'two_factor_secret',
    'app_authentication_recovery_codes',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'app_authentication_recovery_codes' => 'array',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_users')
            ->withPivot(['id', 'role_id', 'is_owner', 'joined_at', 'expires_at']);
    }

    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceUser::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activeWorkspaceMemberships(): HasMany
    {
        return $this->workspaceMemberships()
            ->where(function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->activeWorkspaceMemberships()
            ->where('workspace_id', $tenant->getKey())
            ->exists();
    }

    public function firstAccessibleWorkspace(): ?Workspace
    {
        $membership = $this->activeWorkspaceMemberships()
            ->with('workspace')
            ->orderBy('joined_at')
            ->first();

        return $membership?->workspace;
    }

    public function hasWorkspaceRole(string $workspaceId, string|array $roleSlugs): bool
    {
        return $this->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', Arr::wrap($roleSlugs)))
            ->exists();
    }

    public function canInWorkspace(string $module, string $action, ?string $workspaceId): bool
    {
        if (! $this->is_active || blank($workspaceId)) {
            return false;
        }

        return $this->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->where(function ($query) use ($module, $action): void {
                $query
                    ->where('is_owner', true)
                    ->orWhereHas('role', fn ($roleQuery) => $roleQuery->whereIn('slug', ['owner', 'admin']))
                    ->orWhereHas('role.permissions', function ($permissionQuery) use ($module, $action): void {
                        $permissionQuery
                            ->where('module', $module)
                            ->where('action', $action);
                    });
            })
            ->exists();
    }

}
