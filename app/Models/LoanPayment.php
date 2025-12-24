<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'principal_paid',
        'charges_paid',
        'total_paid',
        'payment_date',
        'payment_method',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'principal_paid' => 'decimal:2',
        'charges_paid' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
