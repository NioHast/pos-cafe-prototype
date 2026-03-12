<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    protected $table = 'cafe_tables';

    protected $fillable = ['table_number', 'qr_code', 'is_available'];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
