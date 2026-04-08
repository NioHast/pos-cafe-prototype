<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    const UNITS = [
        'gram'   => 'Gram (g)',
        'kg'     => 'Kilogram (kg)',
        'ml'     => 'Mililiter (ml)',
        'liter'  => 'Liter (L)',
        'pcs'    => 'Buah / Pcs',
        'sachet' => 'Sachet',
        'sdm'    => 'Sendok Makan (sdm)',
        'sdt'    => 'Sendok Teh (sdt)',
    ];

    protected $fillable = [
        'name',
        'unit',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active ingredients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the batches for the ingredient.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(IngredientBatch::class);
    }

    /**
     * Get the menu items that use this ingredient.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_ingredients')
            ->withPivot('quantity_used');
    }

    /**
     * Get the waste records for the ingredient.
     */
    public function wasteRecords(): HasMany
    {
        return $this->hasMany(WasteRecord::class);
    }

    /**
     * Get stock movement entries for this ingredient.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get stock adjustments for this ingredient.
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Calculate total stock from all batches.
     */
    public function getTotalStock(): float
    {
        return $this->batches()->sum('quantity');
    }
}
