<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'project_id',
        'title',
        'department',
        'amount',
        'status',
        'receipt_path',
        'notes',
        'approved_by',
        'paid_account_id',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'paid_account_id');
    }
}
