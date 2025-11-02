<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSchedule extends Model
{
    protected $fillable = [
        'job_name',
        'frequency',
        'last_run',
        'next_run',
        'status',
        'description',
    ];

    protected $casts = [
        'last_run' => 'datetime',
        'next_run' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function shouldRun(): bool
    {
        return $this->isActive() && $this->next_run->isPast();
    }
}
