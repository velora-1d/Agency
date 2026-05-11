<?php

namespace App\Models\Concerns;

use App\Models\ActivityFeed;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn (Model $record) => $record->writeAuditLog('create'));
        static::updated(fn (Model $record) => $record->writeAuditLog('update'));
        static::deleted(fn (Model $record) => $record->writeAuditLog('delete'));
    }

    protected function writeAuditLog(string $action): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $oldValues = match ($action) {
            'create' => null,
            'update' => $this->sanitizeAuditPayload(
                array_intersect_key($this->getOriginal(), $this->getChanges()),
            ),
            'delete' => $this->sanitizeAuditPayload($this->getOriginal()),
            default => null,
        };

        $newValues = match ($action) {
            'create' => $this->sanitizeAuditPayload($this->getAttributes()),
            'update' => $this->sanitizeAuditPayload($this->getChanges()),
            'delete' => null,
            default => null,
        };

        if ($action === 'update' && $newValues === null) {
            return;
        }

        AuditLog::query()->create([
            'workspace_id' => $this->resolveAuditWorkspaceId(),
            'user_id' => $user->getKey(),
            'module' => Str::of(class_basename($this))->snake()->plural()->toString(),
            'action' => $action,
            'model_type' => $this::class,
            'model_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        if (! $this instanceof AuditLog && ! $this instanceof ActivityFeed) {
            $subjectName = match (true) {
                method_exists($this, 'getAuditName') => $this->getAuditName(),
                ! is_null($this->getAttribute('name')) => $this->getAttribute('name'),
                ! is_null($this->getAttribute('title')) => $this->getAttribute('title'),
                ! is_null($this->getAttribute('number')) => '#' . $this->getAttribute('number'),
                ! is_null($this->getAttribute('invoice_number')) => '#' . $this->getAttribute('invoice_number'),
                default => null,
            };

            $module = Str::lower(class_basename($this));
            $description = ucfirst($action) . ' ' . class_basename($this) . ($subjectName ? ': ' . $subjectName : '');

            $activity = ActivityFeed::query()->create([
                'workspace_id' => $this->resolveAuditWorkspaceId(),
                'user_id' => $user->getKey(),
                'type' => $module,
                'subject_type' => $this::class,
                'subject_id' => $this->getKey(),
                'description' => $description,
                'metadata' => [
                    'action' => $action,
                    'icon' => $this->getAuditIcon($module),
                    'color' => $this->getAuditColor($module),
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                ],
            ]);

            \App\Events\ActivityFeedCreated::dispatch($activity);
        }
    }

    protected function resolveAuditWorkspaceId(): ?string
    {
        if ($this instanceof Workspace) {
            return $this->getKey();
        }

        if (filled($this->getAttribute('workspace_id'))) {
            return (string) $this->getAttribute('workspace_id');
        }

        $workspace = WorkspaceContext::current();

        if ($workspace instanceof Workspace) {
            return $workspace->getKey();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed> | null
     */
    protected function sanitizeAuditPayload(array $payload): ?array
    {
        $sanitized = collect($payload)
            ->except([
                'password',
                'remember_token',
                'two_factor_secret',
                'app_authentication_recovery_codes',
                'smtp_password',
                'wa_api_key',
            ])
            ->all();

        return $sanitized === [] ? null : $sanitized;
    }

    protected function getAuditIcon(string $module): string
    {
        return match ($module) {
            'lead' => 'UserCheck',
            'project' => 'FolderPlus',
            'invoice' => 'CreditCard',
            'meeting' => 'Calendar',
            'task' => 'CheckCircle',
            default => 'Activity',
        };
    }

    protected function getAuditColor(string $module): string
    {
        return match ($module) {
            'lead' => 'green',
            'project' => 'blue',
            'invoice' => 'emerald',
            'meeting' => 'orange',
            'task' => 'purple',
            default => 'slate',
        };
    }
}
