<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()
            ->pluck('id', 'slug');

        $menuCatalog = [
            ['category' => 'kopi-robusta', 'name' => 'Espresso', 'price' => 8000, 'student_price' => 7000],
            ['category' => 'kopi-robusta', 'name' => 'Americano', 'price' => 8000, 'student_price' => 7000],
            ['category' => 'kopi-robusta', 'name' => 'Es Americano', 'price' => 11000, 'student_price' => 10000],
            ['category' => 'kopi-robusta', 'name' => 'Kopi Susu Panas', 'price' => 11000, 'student_price' => 10000],
            ['category' => 'kopi-robusta', 'name' => 'Es Kopi Susu', 'price' => 12000, 'student_price' => 11000],

            ['category' => 'teh', 'name' => 'Teh Tawar Panas', 'price' => 2000, 'student_price' => 1000],
            ['category' => 'teh', 'name' => 'Teh Manis Panas', 'price' => 3000, 'student_price' => 2000],
            ['category' => 'teh', 'name' => 'Es Teh Tawar', 'price' => 3000, 'student_price' => 2000],
            ['category' => 'teh', 'name' => 'Es Teh Manis', 'price' => 4000, 'student_price' => 3000],
            ['category' => 'teh', 'name' => 'Teh Susu Manis Panas', 'price' => 6000, 'student_price' => 5000],
            ['category' => 'teh', 'name' => 'Es Teh Susu Manis', 'price' => 7000, 'student_price' => 6000],

            ['category' => 'jeruk', 'name' => 'Jeruk Nipis Panas', 'price' => 4000, 'student_price' => 3000],
            ['category' => 'jeruk', 'name' => 'Es Jeruk Nipis', 'price' => 5000, 'student_price' => 4000],
            ['category' => 'jeruk', 'name' => 'Lime Tea Panas', 'price' => 5000, 'student_price' => 4000],
            ['category' => 'jeruk', 'name' => 'Es Lime Tea', 'price' => 6000, 'student_price' => 5000],

            ['category' => 'coklat-matcha', 'name' => 'Coklat Panas', 'price' => 7000, 'student_price' => 6000],
            ['category' => 'coklat-matcha', 'name' => 'Es Coklat', 'price' => 8000, 'student_price' => 7000],
            ['category' => 'coklat-matcha', 'name' => 'Matcha Panas', 'price' => 7000, 'student_price' => 6000],
            ['category' => 'coklat-matcha', 'name' => 'Es Matcha', 'price' => 8000, 'student_price' => 7000],

            ['category' => 'minuman-botol', 'name' => 'Sereh Jahe Botol', 'price' => 7000, 'student_price' => 6000],
            ['category' => 'minuman-botol', 'name' => 'Kopi Susu Botol', 'price' => 10000, 'student_price' => 8000],
            ['category' => 'minuman-botol', 'name' => 'Fruit Tea', 'price' => 5000, 'student_price' => 5000],
            ['category' => 'minuman-botol', 'name' => 'Air Mineral', 'price' => 5000, 'student_price' => 5000],

            ['category' => 'cemilan', 'name' => 'Pisang Coklat Keju', 'price' => 7000, 'student_price' => 6000],
            ['category' => 'cemilan', 'name' => 'Tempe Mendoan', 'price' => 8000, 'student_price' => 7000],
            ['category' => 'cemilan', 'name' => 'Kentang (French Fries)', 'price' => 11000, 'student_price' => 10000],

            ['category' => 'mie-spaghetti', 'name' => 'Mie Goreng Telur', 'price' => 10000, 'student_price' => 9000],
            ['category' => 'mie-spaghetti', 'name' => 'Mie Rebus Telur', 'price' => 10000, 'student_price' => 9000],

            ['category' => 'nasi-goreng', 'name' => 'Nasgor Telur', 'price' => 12000, 'student_price' => 10000],
            ['category' => 'nasi-goreng', 'name' => 'Nasgor Ayam + Telur', 'price' => 16000, 'student_price' => 14000],
            ['category' => 'nasi-goreng', 'name' => 'Nasgor Udang + Telur', 'price' => 17000, 'student_price' => 15000],

            ['category' => 'nasi-telur', 'name' => 'Nasi Telur + Teh/Es Teh', 'price' => 10000, 'student_price' => 10000],
        ];

        $now = now();
        $payload = [];

        foreach ($menuCatalog as $item) {
            $categoryId = $categories[$item['category']] ?? null;

            if (! $categoryId) {
                continue;
            }

            $price = (float) $item['price'];
            $studentPrice = (float) $item['student_price'];
            $cashback = max(0, (int) ($price - $studentPrice));

            $payload[] = [
                'category_id' => $categoryId,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'description' => null,
                'price' => $price,
                'cashback' => $cashback,
                'image' => null,
                'is_available' => true,
                'is_student_discount' => $studentPrice < $price,
                'student_price' => $studentPrice,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Menu::query()->upsert(
            $payload,
            ['slug'],
            [
                'category_id',
                'name',
                'description',
                'price',
                'cashback',
                'image',
                'is_available',
                'is_student_discount',
                'student_price',
                'updated_at',
            ]
        );
    }
}
