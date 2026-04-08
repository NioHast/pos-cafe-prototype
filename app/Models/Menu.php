<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $table = 'menu';

    protected $fillable = [
        'name',
        'description',
        'price',
        'student_price',
        'status',
        'category_id',
        'is_active',
        'is_stock_calculated',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'student_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_stock_calculated' => 'boolean',
    ];

    /**
     * Scope a query to only include active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the category that owns the menu.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the ingredients for the menu.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredients')
            ->withPivot('quantity_used');
    }

    /**
     * Get the menu ingredients (pivot records).
     */
    public function menuIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    /**
     * Calculate the cost of goods sold (HPP) for this menu item.
     */
    public function calculateCost(): float
    {
        $totalCost = 0;

        foreach ($this->menuIngredients as $menuIngredient) {
            $ingredient = $menuIngredient->ingredient;
            
            // Get the average cost from available batches
            $averageCost = $ingredient->batches()
                ->where('quantity', '>', 0)
                ->avg('cost_per_unit') ?? 0;

            $totalCost += $averageCost * $menuIngredient->quantity_used;
        }

        return round($totalCost, 2);
    }

    /**
     * Check if menu has a recipe (ingredients assigned).
     */
    public function hasRecipe(): bool
    {
        return $this->menuIngredients()->exists();
    }

    /**
     * Check if menu item is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Refresh stock-calculated flag based on recipe existence.
     */
    public function refreshStockCalculatedFlag(): void
    {
        $this->update([
            'is_stock_calculated' => $this->menuIngredients()->exists(),
        ]);
    }
}
