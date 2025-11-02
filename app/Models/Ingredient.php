<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'low_stock_threshold',
    ];

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
     * Calculate total stock from all batches.
     */
    public function getTotalStock(): float
    {
        return $this->batches()->sum('quantity');
    }
}
