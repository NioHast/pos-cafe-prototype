<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    protected $fillable = [
        'ingredient_id',
        'quantity',
        'reason',
        'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the ingredient that owns the waste record.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Get the user who recorded the waste.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
