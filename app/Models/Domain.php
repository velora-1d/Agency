<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Domain extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'domain_name',
        'registrar',
        'registration_date',
        'expiry_date',
        'status',
        'auto_renew',
        'dns_records',
        'last_whois_at',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'auto_renew' => 'boolean',
        'dns_records' => 'array',
        'last_whois_at' => 'datetime',
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
