<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'title',
        'slug',
        'category',
        'content',
        'is_published',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'view_count' => 'integer',
    ];
}
