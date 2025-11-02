<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientBatch extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ingredient_id',
        'quantity',
        'expiry_date',
        'received_at',
        'cost_per_unit',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_at' => 'datetime',
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    /**
     * Get the ingredient that owns the batch.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
