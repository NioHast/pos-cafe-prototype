<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Minuman'],
            ['name' => 'Makanan'],
            ['name' => 'Snack'],
            ['name' => 'Dessert'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate($category);
        }
    }
}
