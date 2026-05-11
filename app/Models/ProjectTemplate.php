<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'default_tasks',
        'default_finance_split',
    ];

    protected $casts = [
        'default_tasks' => 'array',
        'default_finance_split' => 'array',
        'created_at' => 'datetime',
    ];
}
