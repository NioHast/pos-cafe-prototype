<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_start',
        'shift_end',
        'total_sales',
        'total_transactions',
    ];

    protected $casts = [
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
        'total_sales' => 'decimal:2',
    ];

    /**
     * Get the user (cashier) for this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if session is still active
     */
    public function isActive(): bool
    {
        return is_null($this->shift_end);
    }

    /**
     * Get session duration in hours
     */
    public function getDurationAttribute(): ?float
    {
        if (is_null($this->shift_end)) {
            return null;
        }

        return $this->shift_start->diffInHours($this->shift_end);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereNull('shift_end');
    }

    /**
     * Scope for today's sessions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('shift_start', today());
    }
}
