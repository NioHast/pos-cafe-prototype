<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cashback',
        'image',
        'is_available',
        'is_student_discount',
        'student_price',
    ];

    protected function casts(): array
    {
        return [
            'price'               => 'decimal:2',
            'cashback'            => 'integer',
            'student_price'       => 'decimal:2',
            'is_available'        => 'boolean',
            'is_student_discount' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getEffectivePriceAttribute(): string
    {
        return ($this->is_student_discount && $this->student_price)
            ? $this->student_price
            : $this->price;
    }
}
