<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LoanCharge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_id',
        'month_number',
        'charge_amount',
        'due_date',
        'status',
        'amount_paid',
        'paid_at',
        'payment_notes',
    ];

    protected $casts = [
        'charge_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Check if charge is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'paid' || $this->status === 'waived') {
            return false;
        }

        return Carbon::parse($this->due_date)->isPast();
    }

    /**
     * Mark charge as overdue if past due date
     */
    public function markOverdueIfNeeded(): void
    {
        if ($this->status === 'pending' && $this->isOverdue()) {
            $this->update(['status' => 'overdue']);
        }
    }

    /**
     * Get number of days overdue
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return Carbon::parse($this->due_date)->diffInDays(now());
    }

    /**
     * Check if charge can be paid
     */
    public function canBePaid(): bool
    {
        return in_array($this->status, ['pending', 'overdue']);
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmount(): float
    {
        return $this->charge_amount - $this->amount_paid;
    }
}
