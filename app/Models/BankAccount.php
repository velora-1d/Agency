<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory, HasUuids, BelongsToWorkspace;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'name',
        'bank_name',
        'account_number',
        'account_holder',
        'type',
        'balance',
        'is_active',
        'last_reconciled_at',
        'reconciliation_notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_reconciled_at' => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function recalculateBalance(): void
    {
        $income = $this->transactions()->where('type', 'income')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->sum('amount');
        $this->updateQuietly(['balance' => $income - $expense]);
    }
}
