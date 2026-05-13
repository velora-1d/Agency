<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'lead_id',
        'company_name',
        'pic_name',
        'email',
        'phone',
        'industry',
        'address',
        'city',
        'province',
        'status',
        'assigned_to',
        'notes',
        'portal_access',
        'portal_token',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function domains(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Domain::class, Website::class, 'client_id', 'id', 'id', 'domain_id');
    }

    public function servers(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Server::class, Website::class, 'client_id', 'id', 'id', 'server_id');
    }

    public function activityFeed(): MorphMany
    {
        return $this->morphMany(ActivityFeed::class, 'subject');
    }
}
