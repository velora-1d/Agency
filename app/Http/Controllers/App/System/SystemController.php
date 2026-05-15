<?php

namespace App\Http\Controllers\App\System;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\HelpArticle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Response;

class SystemController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace): Response
    {
        $activeTab = $request->string('tab')->toString() ?: 'roles';

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->map(fn (Permission $permission): array => [
                'id' => $permission->id,
                'module' => $permission->module,
                'action' => $permission->action,
                'description' => $permission->description,
            ]);

        $roles = Role::query()
            ->where('workspace_id', $workspace->id)
            ->with(['permissions:id,module,action', 'parentRole:id,name'])
            ->withCount('childRoles')
            ->get()
            ->map(function (Role $role): array {
                $permissionItems = $role->permissions
                    ->map(fn (Permission $permission): array => [
                        'id' => $permission->id,
                        'label' => sprintf('%s:%s', $permission->module, $permission->action),
                    ])
                    ->values();

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'is_default' => $role->is_default,
                    'parent_role_id' => $role->parent_role_id,
                    'parent_role_name' => $role->parentRole?->name,
                    'permission_ids' => $permissionItems->pluck('id')->all(),
                    'permission_labels' => $permissionItems->pluck('label')->all(),
                    'permission_count' => $permissionItems->count(),
                    'child_roles_count' => $role->child_roles_count,
                    'created_at_label' => optional($role->created_at)?->format('d M Y H:i') ?? '-',
                ];
            });

        $memberships = WorkspaceUser::query()
            ->where('workspace_id', $workspace->id)
            ->with(['user:id,name,email,is_active,two_factor_enabled,last_login_at,last_login_ip', 'role:id,name,slug'])
            ->latest('joined_at')
            ->get()
            ->map(fn (WorkspaceUser $membership): array => [
                'id' => $membership->id,
                'user_id' => $membership->user_id,
                'role_id' => $membership->role_id,
                'is_owner' => $membership->is_owner,
                'joined_at' => optional($membership->joined_at)?->format('Y-m-d\TH:i'),
                'joined_at_label' => optional($membership->joined_at)?->format('d M Y H:i') ?? '-',
                'expires_at' => optional($membership->expires_at)?->format('Y-m-d\TH:i'),
                'expires_at_label' => optional($membership->expires_at)?->format('d M Y H:i') ?? 'No expiry',
                'is_expired' => filled($membership->expires_at) && $membership->expires_at->isPast(),
                'user' => $membership->user ? [
                    'id' => $membership->user->id,
                    'name' => $membership->user->name,
                    'email' => $membership->user->email,
                    'is_active' => $membership->user->is_active,
                    'two_factor_enabled' => $membership->user->two_factor_enabled,
                    'last_login_at_label' => optional($membership->user->last_login_at)?->format('d M Y H:i') ?? 'No login yet',
                    'last_login_ip' => $membership->user->last_login_ip,
                ] : null,
                'role' => $membership->role ? [
                    'id' => $membership->role->id,
                    'name' => $membership->role->name,
                    'slug' => $membership->role->slug,
                ] : null,
            ]);

        $auditLogs = AuditLog::query()
            ->where('workspace_id', $workspace->id)
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(60)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'module' => $log->module,
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'ip_address' => $log->ip_address,
                'user_agent' => Str::limit((string) $log->user_agent, 90),
                'old_values' => $log->old_values ?? [],
                'new_values' => $log->new_values ?? [],
                'old_values_text' => $this->jsonText($log->old_values),
                'new_values_text' => $this->jsonText($log->new_values),
                'summary' => $this->buildAuditSummary($log),
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                ] : null,
                'created_at_label' => optional($log->created_at)?->format('d M Y H:i:s') ?? '-',
            ]);

        $apiKeys = ApiKey::query()
            ->where('workspace_id', $workspace->id)
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn (ApiKey $apiKey): array => [
                'id' => $apiKey->id,
                'user_id' => $apiKey->user_id,
                'name' => $apiKey->name,
                'key_preview' => Str::limit($apiKey->key_hash, 16, '...'),
                'scopes' => $apiKey->scopes ?? [],
                'scopes_text' => implode("\n", $apiKey->scopes ?? []),
                'ip_whitelist' => $apiKey->ip_whitelist ?? [],
                'ip_whitelist_text' => implode("\n", $apiKey->ip_whitelist ?? []),
                'rate_limit_per_hour' => $apiKey->rate_limit_per_hour,
                'last_used_at_label' => optional($apiKey->last_used_at)?->format('d M Y H:i') ?? 'Never used',
                'expires_at' => optional($apiKey->expires_at)?->format('Y-m-d\TH:i'),
                'expires_at_label' => optional($apiKey->expires_at)?->format('d M Y H:i') ?? 'No expiry',
                'is_active' => $apiKey->is_active,
                'user' => $apiKey->user ? [
                    'id' => $apiKey->user->id,
                    'name' => $apiKey->user->name,
                ] : null,
            ]);

        $helpArticles = HelpArticle::query()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->get()
            ->map(fn (HelpArticle $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $article->category,
                'content' => $article->content,
                'excerpt' => Str::limit(trim(strip_tags($article->content)), 180),
                'is_published' => $article->is_published,
                'view_count' => $article->view_count,
                'updated_at_label' => optional($article->updated_at)?->diffForHumans(),
            ]);

        $userIds = $memberships->pluck('user_id')->filter()->values();
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->whereIn('sessions.user_id', $userIds)
            ->orderByDesc('sessions.last_activity')
            ->limit(30)
            ->get([
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->map(fn ($session): array => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'user_name' => $session->user_name,
                'user_email' => $session->user_email,
                'ip_address' => $session->ip_address,
                'user_agent' => Str::limit((string) $session->user_agent, 100),
                'last_activity_label' => now()->setTimestamp((int) $session->last_activity)->format('d M Y H:i'),
            ]);

        $settings = $workspace->settings ?? [];
        $security = data_get($settings, 'security', []);

        $workspacePayload = [
            'id' => $workspace->id,
            'name' => $workspace->name,
            'slug' => $workspace->slug,
            'logo' => $workspace->logo,
            'primary_color' => $workspace->primary_color,
            'timezone' => $workspace->timezone,
            'currency' => $workspace->currency,
            'language' => $workspace->language,
            'custom_domain' => $workspace->custom_domain,
            'smtp_host' => $workspace->smtp_host,
            'smtp_port' => $workspace->smtp_port,
            'smtp_username' => $workspace->smtp_username,
            'wa_api_key' => $workspace->wa_api_key,
            'wa_phone_number' => $workspace->wa_phone_number,
            'n8n_webhook_url' => $workspace->n8n_webhook_url,
            'working_hours_start' => $workspace->working_hours_start,
            'working_hours_end' => $workspace->working_hours_end,
            'storage_quota_gb' => $workspace->storage_quota_gb,
            'holiday_dates' => data_get($settings, 'holiday_dates', []),
            'holiday_dates_text' => implode("\n", data_get($settings, 'holiday_dates', [])),
            'notification_templates' => data_get($settings, 'notification_templates', []),
            'notification_templates_text' => implode("\n", data_get($settings, 'notification_templates', [])),
            'backup_snapshots' => data_get($settings, 'backup_snapshots', []),
            'backup_snapshots_text' => implode("\n", data_get($settings, 'backup_snapshots', [])),
            'invoice_wa_template' => data_get($settings, 'invoice_wa_template', ''),
            'allowed_ips' => data_get($security, 'allowed_ips', []),
            'allowed_ips_text' => implode("\n", data_get($security, 'allowed_ips', [])),
            'require_two_factor' => (bool) data_get($security, 'require_two_factor', false),
            'allow_google_sso' => (bool) data_get($security, 'allow_google_sso', true),
            'brute_force_protection' => (bool) data_get($security, 'brute_force_protection', true),
            'session_idle_minutes' => (int) data_get($security, 'session_idle_minutes', 30),
            'password_policy' => (string) data_get($security, 'password_policy', 'Minimum 8 chars, mixed case, and one number.'),
        ];

        return $this->appShell(
            workspace: $workspace,
            screen: 'System/Index',
            title: 'System',
            activeLabel: 'System',
            payload: [
                'activeTab' => $activeTab,
                'workspaceSettings' => $workspacePayload,
                'roles' => $roles,
                'permissions' => $permissions,
                'memberships' => $memberships,
                'auditLogs' => $auditLogs,
                'apiKeys' => $apiKeys,
                'helpArticles' => $helpArticles,
                'sessions' => $sessions,
                'options' => [
                    'users' => User::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'email'])
                        ->map(fn (User $user): array => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ]),
                    'roleOptions' => $roles->map(fn (array $role): array => [
                        'id' => $role['id'],
                        'name' => $role['name'],
                    ])->values(),
                    'permissionModules' => $permissions->groupBy('module')->map->values(),
                ],
                'summary' => [
                    'roles' => [
                        'total' => $roles->count(),
                        'default' => $roles->where('is_default', true)->count(),
                        'custom' => $roles->where('is_default', false)->count(),
                        'temporary_members' => $memberships->filter(fn (array $item) => filled($item['expires_at']))->count(),
                    ],
                    'settings' => [
                        'configured_integrations' => collect([
                            $workspace->smtp_host,
                            $workspace->wa_api_key,
                            $workspace->n8n_webhook_url,
                            $workspace->custom_domain,
                        ])->filter()->count(),
                        'holiday_count' => count($workspacePayload['holiday_dates']),
                        'template_count' => count($workspacePayload['notification_templates']),
                        'backup_count' => count($workspacePayload['backup_snapshots']),
                    ],
                    'audit' => [
                        'total' => $auditLogs->count(),
                        'today' => $auditLogs->filter(fn (array $item) => str_starts_with($item['created_at_label'], now()->format('d M Y')))->count(),
                        'delete_actions' => $auditLogs->where('action', 'delete')->count(),
                        'users' => $auditLogs->pluck('user.id')->filter()->unique()->count(),
                    ],
                    'security' => [
                        'api_keys' => $apiKeys->count(),
                        'active_keys' => $apiKeys->where('is_active', true)->count(),
                        'active_sessions' => $sessions->count(),
                        'two_factor_users' => $memberships->filter(fn (array $membership) => data_get($membership, 'user.two_factor_enabled'))->count(),
                    ],
                    'help' => [
                        'articles' => $helpArticles->count(),
                        'published' => $helpArticles->where('is_published', true)->count(),
                        'drafts' => $helpArticles->where('is_published', false)->count(),
                        'categories' => $helpArticles->pluck('category')->filter()->unique()->count(),
                    ],
                ],
            ],
        );
    }

    private function authorizeAdminOrOwner(Request $request, Workspace $workspace): void
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $membership = $user->workspaceMemberships()->where('workspace_id', $workspace->id)->first();
        
        abort_unless($membership && ($membership->is_owner || optional($membership->role)->slug === 'admin'), 403, 'Akses ditolak. Hanya Owner dan Admin yang dapat mengatur Tim & Akses.');
    }

    public function storeRole(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);
        $validated = $this->validateRole($request, $workspace);

        $role = Role::create($validated);
        $role->permissions()->sync($request->input('permission_ids', []));

        return back()->with('success', 'Peran baru berhasil ditambahkan.');
    }

    public function updateRole(Request $request, Workspace $workspace, Role $role): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);
        abort_unless($role->workspace_id === $workspace->id, 404);

        $validated = $this->validateRole($request, $workspace, $role);
        $role->update($validated);
        $role->permissions()->sync($request->input('permission_ids', []));

        return back()->with('success', 'Peran berhasil diperbarui.');
    }

    public function destroyRole(Request $request, Workspace $workspace, Role $role): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);
        abort_unless($role->workspace_id === $workspace->id, 404);

        if (WorkspaceUser::query()->where('role_id', $role->id)->exists()) {
            return back()->with('error', 'Peran ini masih digunakan oleh anggota tim.');
        }

        $role->delete();

        return back()->with('success', 'Peran berhasil dihapus.');
    }

    public function storeMembership(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);

        $user = null;
        if ($request->filled('email')) {
            // Cek apakah user sudah terdaftar di sistem
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                // Buat user baru jika belum ada akun dengan email tersebut
                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'unique:users,email'],
                    'password' => ['required', 'string', 'min:8'],
                ]);
                
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                ]);
            }
        } elseif ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
        } else {
            return back()->with('error', 'Silakan pilih pengguna atau masukkan detail akun baru.');
        }

        // Cek apakah user sudah menjadi anggota di workspace ini
        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna ini sudah terdaftar sebagai anggota di workspace ini.');
        }

        // Tambahkan ke workspace
        $workspace->users()->attach($user->id, [
            'id' => Str::uuid(),
            'role_id' => $request->role_id,
            'is_owner' => $request->boolean('is_owner'),
            'joined_at' => now(),
            'expires_at' => $request->filled('expires_at') ? $request->expires_at : null,
        ]);

        return back()->with('success', 'Anggota tim baru berhasil ditambahkan.');
    }

    public function updateMembership(Request $request, Workspace $workspace, WorkspaceUser $membership): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);
        abort_unless($membership->workspace_id === $workspace->id, 404);
        $membership->update($this->validateMembership($request, $workspace, $membership));

        return back()->with('success', 'Informasi anggota tim berhasil diperbarui.');
    }

    public function destroyMembership(Request $request, Workspace $workspace, WorkspaceUser $membership): RedirectResponse
    {
        $this->authorizeAdminOrOwner($request, $workspace);
        abort_unless($membership->workspace_id === $workspace->id, 404);
        $membership->delete();

        return back()->with('success', 'Anggota tim berhasil dihapus dari workspace.');
    }

    public function updateSettings(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'timezone' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:3'],
            'language' => ['required', 'string', 'max:5'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'wa_api_key' => ['nullable', 'string', 'max:255'],
            'wa_phone_number' => ['nullable', 'string', 'max:20'],
            'n8n_webhook_url' => ['nullable', 'string', 'max:255'],
            'working_hours_start' => ['required'],
            'working_hours_end' => ['required'],
            'storage_quota_gb' => ['required', 'integer', 'min:1'],
            'holiday_dates_text' => ['nullable', 'string'],
            'notification_templates_text' => ['nullable', 'string'],
            'backup_snapshots_text' => ['nullable', 'string'],
            'invoice_wa_template' => ['nullable', 'string'],
        ]);

        $settings = $workspace->settings ?? [];
        $settings['holiday_dates'] = $this->textToArray($validated['holiday_dates_text'] ?? '');
        $settings['notification_templates'] = $this->textToArray($validated['notification_templates_text'] ?? '');
        $settings['backup_snapshots'] = $this->textToArray($validated['backup_snapshots_text'] ?? '');
        $settings['invoice_wa_template'] = $validated['invoice_wa_template'] ?? '';

        $workspace->update([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? null,
            'primary_color' => $validated['primary_color'] ?? null,
            'timezone' => $validated['timezone'],
            'currency' => $validated['currency'],
            'language' => $validated['language'],
            'custom_domain' => $validated['custom_domain'] ?? null,
            'smtp_host' => $validated['smtp_host'] ?? null,
            'smtp_port' => $validated['smtp_port'] ?? null,
            'smtp_username' => $validated['smtp_username'] ?? null,
            'smtp_password' => $validated['smtp_password'] ?: $workspace->smtp_password,
            'wa_api_key' => $validated['wa_api_key'] ?: $workspace->wa_api_key,
            'wa_phone_number' => $validated['wa_phone_number'] ?? null,
            'n8n_webhook_url' => $validated['n8n_webhook_url'] ?? null,
            'working_hours_start' => $validated['working_hours_start'],
            'working_hours_end' => $validated['working_hours_end'],
            'storage_quota_gb' => $validated['storage_quota_gb'],
            'settings' => $settings,
        ]);

        return back()->with('success', 'Workspace settings updated successfully.');
    }

    public function storeAuditLog(Request $request, Workspace $workspace): RedirectResponse
    {
        AuditLog::create($this->validateAuditLog($request, $workspace));

        return back()->with('success', 'Audit log created successfully.');
    }

    public function updateAuditLog(Request $request, Workspace $workspace, AuditLog $auditLog): RedirectResponse
    {
        abort_unless($auditLog->workspace_id === $workspace->id, 404);
        $auditLog->update($this->validateAuditLog($request, $workspace, $auditLog));

        return back()->with('success', 'Audit log updated successfully.');
    }

    public function destroyAuditLog(Workspace $workspace, AuditLog $auditLog): RedirectResponse
    {
        abort_unless($auditLog->workspace_id === $workspace->id, 404);
        $auditLog->delete();

        return back()->with('success', 'Audit log deleted successfully.');
    }

    public function storeApiKey(Request $request, Workspace $workspace): RedirectResponse
    {
        ApiKey::create($this->validateApiKey($request, $workspace));

        return back()->with('success', 'API key created successfully.');
    }

    public function updateApiKey(Request $request, Workspace $workspace, ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->workspace_id === $workspace->id, 404);
        $apiKey->update($this->validateApiKey($request, $workspace, $apiKey));

        return back()->with('success', 'API key updated successfully.');
    }

    public function destroyApiKey(Workspace $workspace, ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->workspace_id === $workspace->id, 404);
        $apiKey->delete();

        return back()->with('success', 'API key deleted successfully.');
    }

    public function updateSecurity(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'require_two_factor' => ['nullable', 'boolean'],
            'allow_google_sso' => ['nullable', 'boolean'],
            'brute_force_protection' => ['nullable', 'boolean'],
            'session_idle_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'allowed_ips_text' => ['nullable', 'string'],
            'password_policy' => ['required', 'string'],
        ]);

        $settings = $workspace->settings ?? [];
        $settings['security'] = [
            'require_two_factor' => $request->boolean('require_two_factor'),
            'allow_google_sso' => $request->boolean('allow_google_sso'),
            'brute_force_protection' => $request->boolean('brute_force_protection'),
            'session_idle_minutes' => $validated['session_idle_minutes'],
            'allowed_ips' => $this->textToArray($validated['allowed_ips_text'] ?? ''),
            'password_policy' => $validated['password_policy'],
        ];

        $workspace->update(['settings' => $settings]);

        return back()->with('success', 'Security settings updated successfully.');
    }

    public function storeHelpArticle(Request $request, Workspace $workspace): RedirectResponse
    {
        HelpArticle::create($this->validateHelpArticle($request, $workspace));

        return back()->with('success', 'Help article created successfully.');
    }

    public function updateHelpArticle(Request $request, Workspace $workspace, HelpArticle $helpArticle): RedirectResponse
    {
        abort_unless($helpArticle->workspace_id === $workspace->id, 404);
        $helpArticle->update($this->validateHelpArticle($request, $workspace, $helpArticle));

        return back()->with('success', 'Help article updated successfully.');
    }

    public function destroyHelpArticle(Workspace $workspace, HelpArticle $helpArticle): RedirectResponse
    {
        abort_unless($helpArticle->workspace_id === $workspace->id, 404);
        $helpArticle->delete();

        return back()->with('success', 'Help article deleted successfully.');
    }

    protected function validateRole(Request $request, Workspace $workspace, ?Role $role = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'slug' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'slug')
                    ->ignore($role?->id)
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'parent_role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => [Rule::exists('permissions', 'id')],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }

    protected function validateMembership(Request $request, Workspace $workspace, ?WorkspaceUser $membership = null): array
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::unique('workspace_users', 'user_id')
                    ->ignore($membership?->id)
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'is_owner' => ['nullable', 'boolean'],
            'joined_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['is_owner'] = $request->boolean('is_owner');

        return $validated;
    }

    protected function validateAuditLog(Request $request, Workspace $workspace, ?AuditLog $auditLog = null): array
    {
        $validated = $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'module' => ['required', 'string', 'max:50'],
            'action' => ['required', 'string', 'max:50'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'model_id' => ['nullable', 'uuid'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string'],
            'old_values_text' => ['nullable', 'string'],
            'new_values_text' => ['nullable', 'string'],
        ]);

        return [
            'workspace_id' => $workspace->id,
            'user_id' => $validated['user_id'] ?? null,
            'module' => $validated['module'],
            'action' => $validated['action'],
            'model_type' => $validated['model_type'] ?? null,
            'model_id' => $validated['model_id'] ?? null,
            'ip_address' => $validated['ip_address'] ?? $request->ip(),
            'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
            'old_values' => $this->decodeJsonText($validated['old_values_text'] ?? null),
            'new_values' => $this->decodeJsonText($validated['new_values_text'] ?? null),
        ];
    }

    protected function validateApiKey(Request $request, Workspace $workspace, ?ApiKey $apiKey = null): array
    {
        $validated = $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'key_value' => [$apiKey ? 'nullable' : 'required', 'string', 'min:12'],
            'scopes_text' => ['nullable', 'string'],
            'ip_whitelist_text' => ['nullable', 'string'],
            'rate_limit_per_hour' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'workspace_id' => $workspace->id,
            'user_id' => $validated['user_id'] ?? null,
            'name' => $validated['name'],
            'key_hash' => filled($validated['key_value'] ?? null)
                ? hash('sha256', $validated['key_value'])
                : $apiKey?->key_hash,
            'scopes' => $this->textToArray($validated['scopes_text'] ?? ''),
            'ip_whitelist' => $this->textToArray($validated['ip_whitelist_text'] ?? ''),
            'rate_limit_per_hour' => $validated['rate_limit_per_hour'],
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    protected function validateHelpArticle(Request $request, Workspace $workspace, ?HelpArticle $helpArticle = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('help_articles', 'slug')->ignore($helpArticle?->id)],
            'category' => ['nullable', 'string', 'max:50'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'view_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }

    protected function textToArray(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    protected function decodeJsonText(?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['note' => trim((string) $value)];
    }

    protected function jsonText(?array $value): string
    {
        if (blank($value)) {
            return '';
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '';
    }

    protected function buildAuditSummary(AuditLog $log): string
    {
        $userName = $log->user?->name ?? 'Sistem';
        
        $moduleMap = [
            'projects' => 'Proyek',
            'clients' => 'Klien',
            'leads' => 'Prospek',
            'invoices' => 'Tagihan',
            'contracts' => 'Kontrak',
            'tasks' => 'Tugas',
            'support_tickets' => 'Tiket Dukungan',
            'marketing_campaigns' => 'Kampanye',
            'social_posts' => 'Post Sosial',
            'newsletters' => 'Newsletter',
            'users' => 'Pengguna',
            'roles' => 'Peran',
        ];

        $actionMap = [
            'create' => 'membuat',
            'update' => 'memperbarui',
            'delete' => 'menghapus',
            'login' => 'masuk ke sistem',
            'logout' => 'keluar dari sistem',
            'upload' => 'mengunggah',
            'approve' => 'menyetujui',
        ];

        $moduleLabel = $moduleMap[$log->module] ?? ucfirst($log->module);
        $actionLabel = $actionMap[$log->action] ?? $log->action;

        $changes = collect($log->new_values ?? [])
            ->keys()
            ->filter(fn($key) => !in_array($key, ['updated_at', 'created_at', 'id']))
            ->take(2)
            ->map(fn($key) => str_replace('_', ' ', $key))
            ->implode(', ');

        if ($log->action === 'login' || $log->action === 'logout') {
            return sprintf('%s %s.', $userName, $actionLabel);
        }

        return trim(sprintf(
            '%s %s %s%s',
            $userName,
            $actionLabel,
            $moduleLabel,
            filled($changes) ? " (bidang: {$changes})" : ''
        ));
    }
}
