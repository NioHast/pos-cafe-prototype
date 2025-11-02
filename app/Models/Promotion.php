<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'discount_value',
        'min_purchase',
        'start_date',
        'end_date',
        'status',
        'applicable_items',
        'description',
        'usage_limit',
        'usage_count',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'applicable_items' => 'array',
    ];

    public function isActive(): bool
    {
        $now = now();
        return $this->status === 'active' 
            && $this->start_date <= $now 
            && $this->end_date >= $now;
    }

    public function canBeUsed(): bool
    {
        return $this->isActive() 
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }
}
