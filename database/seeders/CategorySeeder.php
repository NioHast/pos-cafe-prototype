<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::truncate();

        $categories = [
            ['name' => 'Coffee Base',     'slug' => 'coffee-base'],
            ['name' => 'Tea Base',        'slug' => 'tea-base'],
            ['name' => 'Lime Base',       'slug' => 'lime-base'],
            ['name' => 'Chocolatos Base', 'slug' => 'chocolatos-base'],
            ['name' => 'Snack',           'slug' => 'snack'],
            ['name' => 'Indomie Base',    'slug' => 'indomie-base'],
            ['name' => 'Nasi Goreng',     'slug' => 'nasi-goreng'],
            ['name' => 'Nasi Telur',      'slug' => 'nasi-telur'],
            ['name' => 'Ayam Geprek',     'slug' => 'ayam-geprek'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_active' => true]));
        }
    }
}
