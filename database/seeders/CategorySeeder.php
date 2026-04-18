<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            'Kopi Robusta',
            'Teh',
            'Jeruk',
            'Coklat & Matcha',
            'Minuman Botol',
            'Cemilan',
            'Mie & Spaghetti',
            'Nasi Goreng',
            'Nasi Telur',
        ];

        $payload = collect($categories)
            ->map(function (string $name) use ($now): array {
                return [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        Category::query()->upsert(
            $payload,
            ['slug'],
            ['name', 'is_active', 'updated_at']
        );
    }
}
