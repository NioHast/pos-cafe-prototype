<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierSession extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'shift_start',
        'shift_end',
        'total_sales',
        'total_transactions',
    ];

    protected function casts(): array
    {
        return [
            'shift_start' => 'datetime',
            'shift_end' => 'datetime',
            'total_sales' => 'decimal:2',
            'total_transactions' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->shift_end === null;
    }

    public function getDurationAttribute(): ?float
    {
        if ($this->shift_end === null || $this->shift_start === null) {
            return null;
        }

        return round($this->shift_start->diffInMinutes($this->shift_end) / 60, 2);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('shift_end');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('shift_start', today());
    }
}