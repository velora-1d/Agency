<?php

namespace App\Http\Controllers\App\DigitalServices;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Project;
use App\Models\Server;
use App\Models\Website;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class DigitalServiceController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace): Response
    {
        $activeTab = $request->string('tab')->toString() ?: 'websites';

        $websites = Website::query()
            ->where('workspace_id', $workspace->id)
            ->with(['client:id,company_name', 'project:id,name', 'server:id,name', 'domain:id,domain_name'])
            ->latest()
            ->get()
            ->map(fn (Website $website) => [
                'id' => $website->id,
                'client_id' => $website->client_id,
                'project_id' => $website->project_id,
                'server_id' => $website->server_id,
                'domain_id' => $website->domain_id,
                'name' => $website->name,
                'url' => $website->url,
                'cms' => $website->cms,
                'php_version' => $website->php_version,
                'status' => $website->status,
                'ssl_enabled' => $website->ssl_enabled,
                'ssl_expiry_date' => optional($website->ssl_expiry_date)?->format('Y-m-d'),
                'ssl_expiry_label' => optional($website->ssl_expiry_date)?->format('d M Y') ?? 'Not set',
                'uptime_percentage' => $website->uptime_percentage,
                'client' => $website->client ? [
                    'id' => $website->client->id,
                    'name' => $website->client->company_name,
                ] : null,
                'project' => $website->project ? [
                    'id' => $website->project->id,
                    'name' => $website->project->name,
                ] : null,
                'server' => $website->server ? [
                    'id' => $website->server->id,
                    'name' => $website->server->name,
                ] : null,
                'domain' => $website->domain ? [
                    'id' => $website->domain->id,
                    'name' => $website->domain->domain_name,
                ] : null,
                'updated_at_label' => optional($website->updated_at)?->diffForHumans(),
            ]);

        $deployments = Deployment::query()
            ->where('workspace_id', $workspace->id)
            ->with(['website:id,name,url', 'server:id,name', 'deployer:id,name'])
            ->latest('deployed_at')
            ->latest()
            ->get()
            ->map(fn (Deployment $deployment) => [
                'id' => $deployment->id,
                'website_id' => $deployment->website_id,
                'server_id' => $deployment->server_id,
                'environment' => $deployment->environment,
                'platform' => $deployment->platform,
                'git_repo' => $deployment->git_repo,
                'git_branch' => $deployment->git_branch,
                'status' => $deployment->status,
                'log' => $deployment->log,
                'deployed_at' => optional($deployment->deployed_at)?->format('Y-m-d\TH:i'),
                'deployed_at_label' => optional($deployment->deployed_at)?->format('d M Y H:i') ?? 'Not deployed',
                'website' => $deployment->website ? [
                    'id' => $deployment->website->id,
                    'name' => $deployment->website->name,
                    'url' => $deployment->website->url,
                ] : null,
                'server' => $deployment->server ? [
                    'id' => $deployment->server->id,
                    'name' => $deployment->server->name,
                ] : null,
                'deployer' => $deployment->deployer ? [
                    'id' => $deployment->deployer->id,
                    'name' => $deployment->deployer->name,
                ] : null,
            ]);

        $domains = Domain::query()
            ->where('workspace_id', $workspace->id)
            ->withCount('websites')
            ->latest('expiry_date')
            ->latest()
            ->get()
            ->map(fn (Domain $domain) => [
                'id' => $domain->id,
                'domain_name' => $domain->domain_name,
                'registrar' => $domain->registrar,
                'registration_date' => optional($domain->registration_date)?->format('Y-m-d'),
                'registration_date_label' => optional($domain->registration_date)?->format('d M Y') ?? 'Unknown',
                'expiry_date' => optional($domain->expiry_date)?->format('Y-m-d'),
                'expiry_date_label' => optional($domain->expiry_date)?->format('d M Y') ?? 'Unknown',
                'status' => $domain->status,
                'auto_renew' => $domain->auto_renew,
                'dns_records' => $domain->dns_records ?? [],
                'dns_records_text' => implode("\n", $domain->dns_records ?? []),
                'websites_count' => $domain->websites_count,
                'updated_at_label' => optional($domain->updated_at)?->diffForHumans(),
            ]);

        $servers = Server::query()
            ->where('workspace_id', $workspace->id)
            ->with(['credentials', 'websites:id,name,url'])
            ->latest()
            ->get()
            ->map(fn (Server $server) => [
                'id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'ssh_port' => $server->ssh_port,
                'ssh_user' => $server->ssh_user,
                'ssh_password' => $server->ssh_password, // Decrypted by cast
                'dokploy_email' => $server->dokploy_email,
                'dokploy_username' => $server->dokploy_username,
                'dokploy_password' => $server->dokploy_password, // Decrypted by cast
                'control_panel_url' => $server->control_panel_url,
                'provider' => $server->provider,
                'type' => $server->type,
                'location' => $server->location,
                'os' => $server->os,
                'status' => $server->status,
                'specs' => $server->specs ?? [],
                'specs_text' => collect($server->specs ?? [])
                    ->map(fn ($value, $key) => sprintf('%s: %s', strtoupper((string) $key), $value))
                    ->implode("\n"),
                'websites_count' => $server->websites->count(),
                'websites' => $server->websites->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])->all(),
                'credentials' => $server->credentials->map(fn ($c) => [
                    'id' => $c->id,
                    'username' => $c->username,
                    'password' => $c->password,
                    'service_name' => $c->service_name,
                ])->values()->all(),
                'last_checked_at_label' => optional($server->last_checked_at)?->format('d M Y H:i') ?? 'Belum dicek',
            ]);

        $forms = Form::query()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->get()
            ->map(function (Form $form) {
                $fields = $form->fields ?? [];
                $settings = $form->settings ?? [];

                return [
                    'id' => $form->id,
                    'name' => $form->name,
                    'fields' => $fields,
                    'fields_text' => collect($fields)
                        ->map(function ($field) {
                            if (is_array($field)) {
                                return $field['label'] ?? $field['name'] ?? 'Field';
                            }

                            return (string) $field;
                        })
                        ->implode("\n"),
                    'settings' => $settings,
                    'embed_code' => $form->embed_code,
                    'auto_create_lead' => $form->auto_create_lead,
                    'submission_count' => $form->submission_count,
                    'success_redirect_url' => $settings['success_redirect_url'] ?? '',
                    'captcha_enabled' => (bool) ($settings['captcha_enabled'] ?? false),
                    'updated_at_label' => optional($form->updated_at)?->diffForHumans(),
                ];
            });

        $submissions = FormSubmission::query()
            ->whereIn('form_id', $forms->pluck('id'))
            ->with('lead:id,name')
            ->latest('submitted_at')
            ->limit(8)
            ->get()
            ->map(fn (FormSubmission $submission) => [
                'id' => $submission->id,
                'form_id' => $submission->form_id,
                'submitted_at_label' => optional($submission->submitted_at)?->format('d M Y H:i') ?? '-',
                'lead' => $submission->lead ? [
                    'id' => $submission->lead->id,
                    'name' => $submission->lead->name,
                ] : null,
                'data_preview' => collect($submission->data ?? [])
                    ->map(fn ($value, $key) => sprintf('%s: %s', $key, is_array($value) ? json_encode($value) : $value))
                    ->take(2)
                    ->implode(' | '),
            ]);

        $clients = Client::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->company_name,
            ]);

        $projects = Project::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
            ]);

        return $this->appShell(
            workspace: $workspace,
            screen: 'DigitalServices/Index',
            title: 'Digital Services',
            activeLabel: 'Digital Services',
            payload: [
                'activeTab' => $activeTab,
                'websites' => $websites,
                'deployments' => $deployments,
                'domains' => $domains,
                'servers' => $servers,
                'forms' => $forms,
                'formSubmissions' => $submissions,
                'options' => [
                    'clients' => $clients,
                    'projects' => $projects,
                    'servers' => $servers->map(fn (array $server) => ['id' => $server['id'], 'name' => $server['name']])->values(),
                    'domains' => $domains->map(fn (array $domain) => ['id' => $domain['id'], 'name' => $domain['domain_name']])->values(),
                    'websites' => $websites->map(fn (array $website) => ['id' => $website['id'], 'name' => $website['name']])->values(),
                    'providers' => ['DigitalOcean', 'Vultr', 'Hetzner', 'AWS', 'Vercel', 'Netlify', 'Cloudflare'],
                    'registrars' => ['Cloudflare', 'GoDaddy', 'Namecheap', 'Niagahoster', 'Rumahweb'],
                    'platforms' => ['VPS', 'Vercel', 'Netlify', 'Cloudflare Pages', 'Shared Hosting'],
                    'cms' => ['Laravel', 'WordPress', 'Next.js', 'Static', 'Custom'],
                    'phpVersions' => ['8.1', '8.2', '8.3'],
                    'fieldTemplates' => ['Full Name', 'Email', 'Phone', 'Company', 'Message', 'Budget'],
                ],
                'summary' => [
                    'websites' => [
                        'total' => $websites->count(),
                        'live' => $websites->where('status', 'live')->count(),
                        'staging' => $websites->where('status', 'staging')->count(),
                        'down' => $websites->where('status', 'down')->count(),
                    ],
                    'deployments' => [
                        'total' => $deployments->count(),
                        'success' => $deployments->where('status', 'success')->count(),
                        'failed' => $deployments->where('status', 'failed')->count(),
                        'pending' => $deployments->where('status', 'pending')->count(),
                    ],
                    'domains' => [
                        'total' => $domains->count(),
                        'auto_renew' => $domains->where('auto_renew', true)->count(),
                        'expiring' => $domains->filter(fn (array $domain) => $domain['expiry_date'] && now()->diffInDays($domain['expiry_date'], false) <= 30)->count(),
                        'active' => $domains->where('status', 'active')->count(),
                    ],
                    'servers' => [
                        'total' => $servers->count(),
                        'active' => $servers->where('status', 'active')->count(),
                        'maintenance' => $servers->where('status', 'maintenance')->count(),
                        'websites' => $servers->sum('websites_count'),
                    ],
                    'forms' => [
                        'total' => $forms->count(),
                        'auto_lead' => $forms->where('auto_create_lead', true)->count(),
                        'submissions' => $forms->sum('submission_count'),
                        'published' => $forms->filter(fn (array $form) => filled($form['embed_code']))->count(),
                    ],
                ],
            ],
        );
    }

    public function storeWebsite(Request $request, Workspace $workspace): RedirectResponse
    {
        Website::create($this->validateWebsite($request, $workspace));

        return back()->with('success', 'Website created successfully.');
    }

    public function updateWebsite(Request $request, Workspace $workspace, Website $website): RedirectResponse
    {
        abort_unless($website->workspace_id === $workspace->id, 404);
        $website->update($this->validateWebsite($request, $workspace));

        return back()->with('success', 'Website updated successfully.');
    }

    public function destroyWebsite(Workspace $workspace, Website $website): RedirectResponse
    {
        abort_unless($website->workspace_id === $workspace->id, 404);
        $website->delete();

        return back()->with('success', 'Website deleted successfully.');
    }

    public function storeDeployment(Request $request, Workspace $workspace): RedirectResponse
    {
        Deployment::create($this->validateDeployment($request, $workspace));

        return back()->with('success', 'Deployment created successfully.');
    }

    public function updateDeployment(Request $request, Workspace $workspace, Deployment $deployment): RedirectResponse
    {
        abort_unless($deployment->workspace_id === $workspace->id, 404);
        $deployment->update($this->validateDeployment($request, $workspace));

        return back()->with('success', 'Deployment updated successfully.');
    }

    public function destroyDeployment(Workspace $workspace, Deployment $deployment): RedirectResponse
    {
        abort_unless($deployment->workspace_id === $workspace->id, 404);
        $deployment->delete();

        return back()->with('success', 'Deployment deleted successfully.');
    }

    public function storeDomain(Request $request, Workspace $workspace): RedirectResponse
    {
        Domain::create($this->validateDomain($request, $workspace));

        return back()->with('success', 'Domain created successfully.');
    }

    public function updateDomain(Request $request, Workspace $workspace, Domain $domain): RedirectResponse
    {
        abort_unless($domain->workspace_id === $workspace->id, 404);
        $domain->update($this->validateDomain($request, $workspace, $domain));

        return back()->with('success', 'Domain updated successfully.');
    }

    public function destroyDomain(Workspace $workspace, Domain $domain): RedirectResponse
    {
        abort_unless($domain->workspace_id === $workspace->id, 404);
        $domain->delete();

        return back()->with('success', 'Domain deleted successfully.');
    }

    public function storeServer(Request $request, Workspace $workspace): RedirectResponse
    {
        Server::create($this->validateServer($request, $workspace));

        return back()->with('success', 'Server created successfully.');
    }

    public function updateServer(Request $request, Workspace $workspace, Server $server): RedirectResponse
    {
        abort_unless($server->workspace_id === $workspace->id, 404);
        $server->update($this->validateServer($request, $workspace));

        return back()->with('success', 'Server updated successfully.');
    }

    public function destroyServer(Workspace $workspace, Server $server): RedirectResponse
    {
        abort_unless($server->workspace_id === $workspace->id, 404);
        $server->delete();

        return back()->with('success', 'Server deleted successfully.');
    }

    public function storeForm(Request $request, Workspace $workspace): RedirectResponse
    {
        Form::create($this->validateForm($request, $workspace));

        return back()->with('success', 'Form created successfully.');
    }

    public function updateForm(Request $request, Workspace $workspace, Form $form): RedirectResponse
    {
        abort_unless($form->workspace_id === $workspace->id, 404);
        $form->update($this->validateForm($request, $workspace));

        return back()->with('success', 'Form updated successfully.');
    }

    public function destroyForm(Workspace $workspace, Form $form): RedirectResponse
    {
        abort_unless($form->workspace_id === $workspace->id, 404);
        $form->delete();

        return back()->with('success', 'Form deleted successfully.');
    }

    protected function validateWebsite(Request $request, Workspace $workspace): array
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'uuid'],
            'project_id' => ['nullable', 'uuid'],
            'server_id' => ['nullable', 'uuid'],
            'domain_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:255'],
            'cms' => ['nullable', 'string', 'max:50'],
            'php_version' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'string', 'max:20'],
            'ssl_enabled' => ['nullable', 'boolean'],
            'ssl_expiry_date' => ['nullable', 'date'],
            'uptime_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['ssl_enabled'] = $request->boolean('ssl_enabled');

        return $validated;
    }

    protected function validateDeployment(Request $request, Workspace $workspace): array
    {
        $validated = $request->validate([
            'website_id' => ['required', 'uuid'],
            'server_id' => ['nullable', 'uuid'],
            'environment' => ['required', 'string', 'max:20'],
            'platform' => ['required', 'string', 'max:20'],
            'git_repo' => ['nullable', 'string', 'max:255'],
            'git_branch' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:20'],
            'log' => ['nullable', 'string'],
            'deployed_at' => ['nullable', 'date'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['deployed_by'] = $request->user()?->id;

        return $validated;
    }

    protected function validateDomain(Request $request, Workspace $workspace, ?Domain $domain = null): array
    {
        $validated = $request->validate([
            'domain_name' => ['required', 'string', 'max:255'],
            'registrar' => ['nullable', 'string', 'max:100'],
            'registration_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:20'],
            'auto_renew' => ['nullable', 'boolean'],
            'dns_records_text' => ['nullable', 'string'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['auto_renew'] = $request->boolean('auto_renew');
        $validated['dns_records'] = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['dns_records_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        unset($validated['dns_records_text']);

        return $validated;
    }

    protected function validateServer(Request $request, Workspace $workspace): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'ssh_port' => ['nullable', 'integer'],
            'ssh_user' => ['nullable', 'string', 'max:50'],
            'ssh_password' => ['nullable', 'string'],
            'dokploy_email' => ['nullable', 'string', 'email', 'max:100'],
            'dokploy_username' => ['nullable', 'string', 'max:100'],
            'dokploy_password' => ['nullable', 'string'],
            'provider' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:50'],
            'os' => ['nullable', 'string', 'max:50'],
            'control_panel_url' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:20'],
            'specs_text' => ['nullable', 'string'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['specs'] = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['specs_text'] ?? '')))
            ->map(function ($line) {
                [$key, $value] = array_pad(explode(':', $line, 2), 2, null);

                if (blank($key) || blank($value)) {
                    return null;
                }

                return [trim(strtolower($key)) => trim($value)];
            })
            ->filter()
            ->reduce(fn ($carry, $item) => array_merge($carry, $item), []);

        unset($validated['specs_text']);

        return $validated;
    }

    protected function validateForm(Request $request, Workspace $workspace): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'fields_text' => ['required', 'string'],
            'success_redirect_url' => ['nullable', 'string', 'max:255'],
            'captcha_enabled' => ['nullable', 'boolean'],
            'embed_code' => ['nullable', 'string'],
            'auto_create_lead' => ['nullable', 'boolean'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['fields'] = collect(preg_split('/\r\n|\r|\n/', (string) $validated['fields_text']))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(fn ($line) => ['label' => $line, 'name' => str($line)->lower()->snake()->toString(), 'type' => 'text'])
            ->values()
            ->all();
        $validated['settings'] = [
            'success_redirect_url' => $validated['success_redirect_url'] ?? null,
            'captcha_enabled' => $request->boolean('captcha_enabled'),
        ];
        $validated['auto_create_lead'] = $request->boolean('auto_create_lead');

        unset($validated['fields_text'], $validated['success_redirect_url'], $validated['captcha_enabled']);

        return $validated;
    }
}
