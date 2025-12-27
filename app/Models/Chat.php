<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'initial_message',
        'status',
        'priority',
        'assigned_to',
        'started_at',
        'closed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->name ?? 'Anonymous';
    }

    public function getDisplayEmailAttribute(): string
    {
        return $this->user?->email ?? $this->email ?? 'No email';
    }

    public function markAsRead(): void
    {
        $this->messages()->where('is_read', false)->update(['is_read' => true]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
