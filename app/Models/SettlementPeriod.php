<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SettlementPeriod extends Model
{
    protected $fillable = [
        'group_id',
        'period_name',
        'start_date',
        'end_date',
        'status',
        'total_savings_target',
        'total_savings_collected',
        'total_interest_earned',
        'total_penalties_applied',
        'total_settlement_amount',
        'finalized_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'finalized_at' => 'datetime',
        'total_savings_target' => 'decimal:2',
        'total_savings_collected' => 'decimal:2',
        'total_interest_earned' => 'decimal:2',
        'total_penalties_applied' => 'decimal:2',
        'total_settlement_amount' => 'decimal:2',
    ];

    // Relationships
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->whereBetween('start_date', [now()->subYear(), now()])
            ->where('status', '!=', 'finalized');
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               Carbon::parse($this->start_date)->isPast() &&
               Carbon::parse($this->end_date)->isFuture();
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function daysRemaining(): int
    {
        return Carbon::parse($this->end_date)->diffInDays(now());
    }

    public function percentageComplete(): float
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $now = now();

        if ($now->isBefore($start)) return 0;
        if ($now->isAfter($end)) return 100;

        $total = $end->diffInDays($start);
        $elapsed = $now->diffInDays($start);

        return round(($elapsed / $total) * 100, 2);
    }

    public function getSettlementSummary(): array
    {
        $settlements = $this->settlements;
        $paidCount = $settlements->where('status', 'paid')->count();
        $totalCount = $settlements->count();

        return [
            'total_members' => $totalCount,
            'settled_members' => $paidCount,
            'pending_members' => $totalCount - $paidCount,
            'settlement_percentage' => $totalCount > 0 ? round(($paidCount / $totalCount) * 100, 2) : 0,
            'total_due' => $settlements->sum('total_due'),
            'total_paid' => $settlements->sum('amount_paid'),
            'total_pending' => $settlements->sum('amount_pending'),
        ];
    }
}
