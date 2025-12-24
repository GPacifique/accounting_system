<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Saving extends Model
{
    protected $table = 'savings';

    protected $fillable = [
        'group_id',
        'member_id',
        'current_balance',
        'total_deposits',
        'total_withdrawals',
        'interest_earned',
        'last_deposit_date',
        'last_withdrawal_date',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'total_deposits' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'last_deposit_date' => 'date',
        'last_withdrawal_date' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class, 'member_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reference');
    }

    /**
     * Add funds to savings
     */
    public function deposit(float $amount, string $description = null): void
    {
        $this->update([
            'current_balance' => $this->current_balance + $amount,
            'total_deposits' => $this->total_deposits + $amount,
            'last_deposit_date' => now()->toDateString(),
        ]);

        // Update member's savings
        $this->member->update([
            'current_savings' => $this->member->current_savings + $amount,
            'total_contributed' => $this->member->total_contributed + $amount,
        ]);

        // Record transaction
        Transaction::create([
            'group_id' => $this->group_id,
            'member_id' => $this->member_id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_after' => $this->current_balance + $amount,
            'description' => $description ?? 'Savings deposit',
            'reference' => $this->id,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    /**
     * Withdraw funds from savings
     */
    public function withdraw(float $amount, string $description = null): bool
    {
        if ($this->current_balance < $amount) {
            return false; // Insufficient balance
        }

        $this->update([
            'current_balance' => $this->current_balance - $amount,
            'total_withdrawals' => $this->total_withdrawals + $amount,
            'last_withdrawal_date' => now()->toDateString(),
        ]);

        // Update member's savings
        $this->member->update([
            'current_savings' => $this->member->current_savings - $amount,
            'total_withdrawn' => $this->member->total_withdrawn + $amount,
        ]);

        // Record transaction
        Transaction::create([
            'group_id' => $this->group_id,
            'member_id' => $this->member_id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_after' => $this->current_balance - $amount,
            'description' => $description ?? 'Savings withdrawal',
            'reference' => $this->id,
            'transaction_date' => now()->toDateString(),
        ]);

        return true;
    }

    /**
     * Add interest to savings
     */
    public function addInterest(float $amount): void
    {
        $this->update([
            'current_balance' => $this->current_balance + $amount,
            'interest_earned' => $this->interest_earned + $amount,
        ]);

        Transaction::create([
            'group_id' => $this->group_id,
            'member_id' => $this->member_id,
            'type' => 'interest',
            'amount' => $amount,
            'balance_after' => $this->current_balance + $amount,
            'description' => 'Interest earned',
            'reference' => $this->id,
            'transaction_date' => now()->toDateString(),
        ]);
    }
}
