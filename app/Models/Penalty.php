<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penalty extends Model
{
    protected $fillable = [
        'member_id',
        'group_id',
        'loan_id',
        'type',
        'amount',
        'reason',
        'waived',
        'waived_reason',
        'applied_at',
        'waived_at',
        'waived_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'waived' => 'boolean',
        'applied_at' => 'datetime',
        'waived_at' => 'datetime',
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class, 'member_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function waivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('waived', false);
    }

    public function scopeWaived($query)
    {
        return $query->where('waived', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function isWaived(): bool
    {
        return $this->waived === true;
    }

    public function waive(string $reason, int $waivedByUserId): void
    {
        $this->update([
            'waived' => true,
            'waived_reason' => $reason,
            'waived_at' => now(),
            'waived_by' => $waivedByUserId,
        ]);
    }

    public function getEffectiveAmount(): float
    {
        return $this->waived ? 0 : (float) $this->amount;
    }

    public static function getTypeLabel(string $type): string
    {
        return match($type) {
            'late_payment' => 'Late Payment',
            'violation' => 'Rule Violation',
            'default' => 'Default',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
