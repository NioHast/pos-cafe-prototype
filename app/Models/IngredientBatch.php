<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'quantity' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
