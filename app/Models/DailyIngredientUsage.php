<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyIngredientUsage extends Model
{
    protected $fillable = [
        'usage_date',
        'ingredient_id',
        'ingredient_name',
        'unit',
        'jumlah_digunakan',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'jumlah_digunakan' => 'decimal:2',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
