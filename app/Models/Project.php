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

class Project extends Model
{
    use HasFactory, HasUuids, Auditable, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'brand',
        'client_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'actual_cost',
        'progress',
        'template_id',
        'tags',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'joined_at');
    }

    public function activityFeed(): MorphMany
    {
        return $this->morphMany(ActivityFeed::class, 'subject');
    }

    public function updateProgress(): void
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
           return;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        $progress = ($completedTasks / $totalTasks) * 100;

        $this->updateQuietly(['progress' => round($progress)]);
    }
}
