<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Settlement extends Model
{
    protected $fillable = [
        'settlement_period_id',
        'member_id',
        'original_savings',
        'interest_earned',
        'penalties_applied',
        'penalties_waived',
        'total_due',
        'amount_paid',
        'amount_pending',
        'status',
        'payment_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'original_savings' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'penalties_applied' => 'decimal:2',
        'penalties_waived' => 'decimal:2',
        'total_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_pending' => 'decimal:2',
        'payment_date' => 'date',
        'due_date' => 'date',
    ];

    // Relationships
    public function settlementPeriod(): BelongsTo
    {
        return $this->belongsTo(SettlementPeriod::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class, 'member_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SettlementPayment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->where('due_date', '<', now()->toDateString());
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date < now()->toDateString() &&
               $this->status !== 'paid';
    }

    public function recordPayment(float $amount, string $paymentMethod, ?string $reference = null, ?string $notes = null, int $recordedByUserId = null): SettlementPayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'notes' => $notes,
            'recorded_by' => $recordedByUserId ?? auth()->id(),
        ]);

        // Update settlement totals
        $newAmountPaid = $this->amount_paid + $amount;
        $newAmountPending = max(0, $this->total_due - $newAmountPaid);
        $newStatus = $newAmountPending == 0 ? 'paid' : 'partial';

        $this->update([
            'amount_paid' => $newAmountPaid,
            'amount_pending' => $newAmountPending,
            'status' => $newStatus,
            'payment_date' => $newStatus === 'paid' ? now()->toDateString() : $this->payment_date,
        ]);

        return $payment;
    }

    public function getTotalNetAmount(): float
    {
        return $this->original_savings +
               $this->interest_earned +
               $this->penalties_applied -
               $this->penalties_waived;
    }

    public function getBreakdown(): array
    {
        return [
            'savings' => (float) $this->original_savings,
            'interest' => (float) $this->interest_earned,
            'penalties' => [
                'applied' => (float) $this->penalties_applied,
                'waived' => (float) $this->penalties_waived,
                'net' => (float) ($this->penalties_applied - $this->penalties_waived),
            ],
            'total_due' => (float) $this->total_due,
            'paid' => (float) $this->amount_paid,
            'pending' => (float) $this->amount_pending,
        ];
    }

    public function getDaysOverdue(): ?int
    {
        if (!$this->isOverdue()) {
            return null;
        }
        return Carbon::parse($this->due_date)->diffInDays(now());
    }
}
