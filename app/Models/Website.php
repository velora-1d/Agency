<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Website extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'project_id',
        'server_id',
        'domain_id',
        'name',
        'url',
        'cms',
        'php_version',
        'status',
        'ssl_enabled',
        'ssl_expiry_date',
        'last_uptime_check_at',
        'uptime_percentage',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'ssl_expiry_date' => 'date',
        'last_uptime_check_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function credentials(): MorphMany
    {
        return $this->morphMany(ServiceCredential::class, 'credentialable');
    }
}
