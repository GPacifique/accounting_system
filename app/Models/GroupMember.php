<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMember extends Model
{
    use SoftDeletes;

    protected $table = 'group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'status',
        'current_savings',
        'total_contributed',
        'total_withdrawn',
        'total_borrowed',
        'total_repaid',
        'outstanding_loans',
        'joined_at',
    ];

    protected $casts = [
        'current_savings' => 'decimal:2',
        'total_contributed' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'total_borrowed' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'outstanding_loans' => 'decimal:2',
        'joined_at' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id');
    }

    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class, 'member_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class, 'member_id');
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'member_id');
    }

    public function getActiveLoanCount(): int
    {
        return $this->loans()->where('status', 'active')->count();
    }

    public function getTotalOutstandingLoans(): float
    {
        return $this->loans()->where('status', 'active')->sum('remaining_balance');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
