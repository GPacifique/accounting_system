<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'member_id',
        'principal_amount',
        'monthly_charge',
        'remaining_balance',
        'duration_months',
        'months_paid',
        'total_charged',
        'total_principal_paid',
        'issued_at',
        'maturity_date',
        'paid_off_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'monthly_charge' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'total_charged' => 'decimal:2',
        'total_principal_paid' => 'decimal:2',
        'issued_at' => 'date',
        'maturity_date' => 'date',
        'paid_off_at' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class, 'member_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(LoanCharge::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reference');
    }

    /**
     * Get pending charges for this loan
     */
    public function pendingCharges(): HasMany
    {
        return $this->charges()->where('status', 'pending');
    }

    /**
     * Get overdue charges
     */
    public function overdueCharges(): HasMany
    {
        return $this->charges()->where('status', 'overdue');
    }

    /**
     * Calculate total outstanding charges
     */
    public function getTotalOutstandingCharges(): float
    {
        return $this->pendingCharges()
            ->sum('charge_amount');
    }

    /**
     * Get next payment due date
     */
    public function getNextDueDate(): ?Carbon
    {
        return $this->charges()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->value('due_date');
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        return $this->overdueCharges()->exists();
    }

    /**
     * Check if loan is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->status === 'completed' &&
               $this->remaining_balance == 0;
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgress(): float
    {
        if ($this->principal_amount == 0) {
            return 0;
        }
        return ($this->total_principal_paid / $this->principal_amount) * 100;
    }

    /**
     * Calculate total cost of loan (principal + all charges)
     */
    public function getTotalLoanCost(): float
    {
        return $this->principal_amount +
               ($this->monthly_charge * $this->duration_months);
    }
}
