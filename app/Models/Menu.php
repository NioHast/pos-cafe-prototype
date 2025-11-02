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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'student_price' => 'decimal:2',
    ];

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
     * Check if menu item is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
