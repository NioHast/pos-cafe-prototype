<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'period_start',
        'period_end',
        'total_income',
        'total_expense',
        'net_profit',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_income == 0) {
            return 0;
        }

        return ($this->net_profit / $this->total_income) * 100;
    }

    /**
     * Scope for this month's report
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('period_start', now()->month)
                     ->whereYear('period_start', now()->year);
    }
}
