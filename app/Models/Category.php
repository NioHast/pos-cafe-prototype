<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the menu items for the category.
     */
    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
