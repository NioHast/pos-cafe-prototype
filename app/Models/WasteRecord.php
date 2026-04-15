<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRecord extends Model
{
    protected $fillable = [
        'ingredient_id',
        'quantity',
        'reason',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
