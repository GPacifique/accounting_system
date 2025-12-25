<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'member_numbers',
        'description',
        'created_by',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'total_savings',
        'total_loans_issued',
        'total_interest_earned',
    ];

    protected $casts = [
        'total_savings' => 'decimal:2',
        'total_loans_issued' => 'decimal:2',
        'total_interest_earned' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function settlementPeriods(): HasMany
    {
        return $this->hasMany(SettlementPeriod::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function activeLoanCount(): int
    {
        return $this->loans()->whereIn('status', ['active', 'pending', 'approved'])->count();
    }

    public function totalActiveLoanBalance(): float
    {
        return $this->loans()
            ->whereIn('status', ['active', 'pending', 'approved'])
            ->sum('remaining_balance');
    }

    public function getTotalSavingsBalance(): float
    {
        return $this->members()->sum('current_savings');
    }
}
