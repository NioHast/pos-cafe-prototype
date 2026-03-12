<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $kopi       = Category::where('slug', 'kopi')->first()->id;
        $teh        = Category::where('slug', 'teh')->first()->id;
        $coklat     = Category::where('slug', 'coklat-dan-matcha')->first()->id;
        $cemilan    = Category::where('slug', 'cemilan')->first()->id;
        $mie        = Category::where('slug', 'mie-dan-nasi-goreng')->first()->id;

        $menus = [
            ['category_id' => $kopi,    'name' => 'Kopi Robusta',          'slug' => 'kopi-robusta',          'price' => 12000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Kopi Latte',            'slug' => 'kopi-latte',            'price' => 20000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Es Kopi Vanilla',       'slug' => 'es-kopi-vanilla',       'price' => 22000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Mocha',                 'slug' => 'mocha',                 'price' => 22000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Kopi Susu Gula Aren',   'slug' => 'kopi-susu-gula-aren',   'price' => 18000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Americano',             'slug' => 'americano',             'price' => 15000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Espresso',              'slug' => 'espresso',              'price' => 10000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $kopi,    'name' => 'Cappuccino',            'slug' => 'cappuccino',            'price' => 20000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $teh,     'name' => 'Teh Manis',             'slug' => 'teh-manis',             'price' =>  8000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $coklat,  'name' => 'Matcha Latte',          'slug' => 'matcha-latte',          'price' => 25000, 'is_student_discount' => true,  'student_price' => 22500],
            ['category_id' => $mie,     'name' => 'Nasi Goreng',           'slug' => 'nasi-goreng',           'price' => 20000, 'is_student_discount' => false, 'student_price' => null],
            ['category_id' => $cemilan, 'name' => 'Keripik',               'slug' => 'keripik',               'price' =>  8000, 'is_student_discount' => false, 'student_price' => null],
        ];

        foreach ($menus as $menu) {
            Menu::create(array_merge($menu, [
                'description'  => null,
                'image'        => null,
                'is_available' => true,
            ]));
        }
    }
}
