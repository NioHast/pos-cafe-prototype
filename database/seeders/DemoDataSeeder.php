<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini untuk membuat data demo lengkap untuk testing.
     * Jalankan dengan: php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        // 1. Create Ingredients
        $kopiIngredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 500,
        ]);

        $susuIngredient = Ingredient::create([
            'name' => 'Susu',
            'unit' => 'ml',
            'low_stock_threshold' => 1000,
        ]);

        $gulaIngredient = Ingredient::create([
            'name' => 'Gula',
            'unit' => 'gram',
            'low_stock_threshold' => 500,
        ]);

        $matchaIngredient = Ingredient::create([
            'name' => 'Matcha Powder',
            'unit' => 'gram',
            'low_stock_threshold' => 200,
        ]);

        $coklatIngredient = Ingredient::create([
            'name' => 'Coklat Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 300,
        ]);

        // 2. Create Ingredient Batches
        // Kopi Bubuk - 2 batches dengan expiry date berbeda
        IngredientBatch::create([
            'ingredient_id' => $kopiIngredient->id,
            'quantity' => 500,
            'expiry_date' => Carbon::now()->addMonths(2), // Expiry lebih dekat
            'received_at' => Carbon::now()->subDays(10),
            'cost_per_unit' => 48,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $kopiIngredient->id,
            'quantity' => 1000,
            'expiry_date' => Carbon::now()->addMonths(6), // Expiry lebih jauh
            'received_at' => Carbon::now()->subDays(5),
            'cost_per_unit' => 50,
        ]);

        // Susu - 2 batches
        IngredientBatch::create([
            'ingredient_id' => $susuIngredient->id,
            'quantity' => 2000,
            'expiry_date' => Carbon::now()->addDays(14),
            'received_at' => Carbon::now()->subDays(7),
            'cost_per_unit' => 15,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $susuIngredient->id,
            'quantity' => 3000,
            'expiry_date' => Carbon::now()->addDays(21),
            'received_at' => Carbon::now()->subDays(2),
            'cost_per_unit' => 16,
        ]);

        // Gula - 1 batch
        IngredientBatch::create([
            'ingredient_id' => $gulaIngredient->id,
            'quantity' => 2000,
            'expiry_date' => Carbon::now()->addYear(),
            'received_at' => Carbon::now()->subDays(30),
            'cost_per_unit' => 12,
        ]);

        // Matcha - 1 batch
        IngredientBatch::create([
            'ingredient_id' => $matchaIngredient->id,
            'quantity' => 300,
            'expiry_date' => Carbon::now()->addMonths(4),
            'received_at' => Carbon::now()->subDays(15),
            'cost_per_unit' => 150,
        ]);

        // Coklat - 1 batch
        IngredientBatch::create([
            'ingredient_id' => $coklatIngredient->id,
            'quantity' => 800,
            'expiry_date' => Carbon::now()->addMonths(8),
            'received_at' => Carbon::now()->subDays(20),
            'cost_per_unit' => 40,
        ]);

        // 3. Get Categories
        $minumanCategory = Category::where('name', 'Minuman')->first();
        
        if (!$minumanCategory) {
            $minumanCategory = Category::create(['name' => 'Minuman']);
        }

        // 4. Create Menu Items with Recipes
        $kopiSusu = Menu::create([
            'name' => 'Kopi Susu',
            'description' => 'Kopi dengan susu segar',
            'price' => 15000,
            'student_price' => 12000,
            'status' => 'available',
            'category_id' => $minumanCategory->id,
        ]);

        // Recipe for Kopi Susu
        MenuIngredient::create([
            'menu_id' => $kopiSusu->id,
            'ingredient_id' => $kopiIngredient->id,
            'quantity_used' => 15, // 15 gram kopi per porsi
        ]);

        MenuIngredient::create([
            'menu_id' => $kopiSusu->id,
            'ingredient_id' => $susuIngredient->id,
            'quantity_used' => 150, // 150 ml susu per porsi
        ]);

        MenuIngredient::create([
            'menu_id' => $kopiSusu->id,
            'ingredient_id' => $gulaIngredient->id,
            'quantity_used' => 10, // 10 gram gula per porsi
        ]);

        // Menu 2: Matcha Latte
        $matchaLatte = Menu::create([
            'name' => 'Matcha Latte',
            'description' => 'Matcha premium dengan susu',
            'price' => 25000,
            'student_price' => 20000,
            'status' => 'available',
            'category_id' => $minumanCategory->id,
        ]);

        MenuIngredient::create([
            'menu_id' => $matchaLatte->id,
            'ingredient_id' => $matchaIngredient->id,
            'quantity_used' => 8, // 8 gram matcha
        ]);

        MenuIngredient::create([
            'menu_id' => $matchaLatte->id,
            'ingredient_id' => $susuIngredient->id,
            'quantity_used' => 200, // 200 ml susu
        ]);

        MenuIngredient::create([
            'menu_id' => $matchaLatte->id,
            'ingredient_id' => $gulaIngredient->id,
            'quantity_used' => 12, // 12 gram gula
        ]);

        // Menu 3: Coklat Panas
        $coklatPanas = Menu::create([
            'name' => 'Coklat Panas',
            'description' => 'Coklat hangat dengan susu',
            'price' => 18000,
            'student_price' => 15000,
            'status' => 'available',
            'category_id' => $minumanCategory->id,
        ]);

        MenuIngredient::create([
            'menu_id' => $coklatPanas->id,
            'ingredient_id' => $coklatIngredient->id,
            'quantity_used' => 20, // 20 gram coklat
        ]);

        MenuIngredient::create([
            'menu_id' => $coklatPanas->id,
            'ingredient_id' => $susuIngredient->id,
            'quantity_used' => 180, // 180 ml susu
        ]);

        MenuIngredient::create([
            'menu_id' => $coklatPanas->id,
            'ingredient_id' => $gulaIngredient->id,
            'quantity_used' => 15, // 15 gram gula
        ]);

        $this->command->info('✅ Demo data berhasil dibuat!');
        $this->command->info('📦 Ingredients: 5 items dengan multiple batches');
        $this->command->info('☕ Menu: 3 items (Kopi Susu, Matcha Latte, Coklat Panas)');
        $this->command->info('');
        $this->command->info('Coba simulasi pesanan sekarang:');
        $this->command->info('- Login: admin@example.com / password');
        $this->command->info('- Buka: Simulasi Pesanan');
        $this->command->info('- Pesan: Kopi Susu x 10 (akan mengurangi dari batch dengan expiry terdekat)');
    }
}
