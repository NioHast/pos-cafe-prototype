<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Kopi',
            'Teh',
            'Coklat & Matcha',
            'Minuman Botol',
            'Cemilan',
            'Mie & Nasi Goreng',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name'      => $name,
                'slug'      => str_replace([' ', '&', '/'], ['-', 'dan', '-'], strtolower($name)),
                'is_active' => true,
            ]);
        }
    }
}
