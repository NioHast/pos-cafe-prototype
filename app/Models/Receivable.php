<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PARTIAL,
        self::STATUS_PAID,
        self::STATUS_OVERDUE,
    ];

    protected $fillable = [
        'customer_name',
        'amount',
        'invoice_date',
        'due_date',
        'status',
        'paid_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date !== null
            && $this->due_date->isPast();
    }
}