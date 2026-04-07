<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $coffee   = Category::where('slug', 'coffee-base')->value('id');
        $tea      = Category::where('slug', 'tea-base')->value('id');
        $lime     = Category::where('slug', 'lime-base')->value('id');
        $choco    = Category::where('slug', 'chocolatos-base')->value('id');
        $snack    = Category::where('slug', 'snack')->value('id');
        $indomie  = Category::where('slug', 'indomie-base')->value('id');
        $nasgor   = Category::where('slug', 'nasi-goreng')->value('id');
        $nastel   = Category::where('slug', 'nasi-telur')->value('id');
        $geprek   = Category::where('slug', 'ayam-geprek')->value('id');

        // [category_id, name, slug, price, cashback]
        $menus = [
            // ── COFFEE BASE ────────────────────────────────────
            [$coffee, 'Espresso',             'espresso',             10000, 2000],
            [$coffee, 'Americano Panas',      'americano-panas',      10000, 2000],
            [$coffee, 'Es Americano',         'es-americano',         12000, 2000],
            [$coffee, 'Kopi Susu',            'kopi-susu',            14000, 2000],

            // ── TEA BASE ───────────────────────────────────────
            [$tea,    'Teh Tawar',            'teh-tawar',             3000, 1000],
            [$tea,    'Teh Manis',            'teh-manis',             4000, 1000],
            [$tea,    'Teh Susu',             'teh-susu',              7000, 2000],

            // ── LIME BASE ──────────────────────────────────────
            [$lime,   'Jeruk Nipis',          'jeruk-nipis',           5000, 1000],
            [$lime,   'Teh Jeruk (Lime Tea)', 'teh-jeruk-lime-tea',    6000, 1000],

            // ── CHOCOLATOS BASE ───────────────────────────────
            [$choco,  'Full Chocolate',       'full-chocolate',        8000, 2000],
            [$choco,  'Matcha',               'matcha',                8000, 2000],
            [$choco,  'Vanilla Latte',        'vanilla-latte',         8000, 2000],
            [$choco,  'Creamy Chocolatey',    'creamy-chocolatey',     8000, 2000],

            // ── SNACK ─────────────────────────────────────────
            [$snack,  'Pisang Coklat Keju',   'pisang-coklat-keju',   10000, 2000],
            [$snack,  'Tempe Mendoan',        'tempe-mendoan',         8000, 2000],
            [$snack,  'Kentang (French Fries)','kentang-french-fries', 12000, 2000],

            // ── INDOMIE BASE ──────────────────────────────────
            [$indomie,'Mie Goreng Telur',     'mie-goreng-telur',     10000, 1000],
            [$indomie,'Mie Rebus Telur',      'mie-rebus-telur',      10000, 1000],

            // ── NASI GORENG ───────────────────────────────────
            [$nasgor, 'Nasgor Telur',         'nasgor-telur',         12000, 2000],
            [$nasgor, 'Nasgor Ayam/Udang',    'nasgor-ayam-udang',    17000, 2000],

            // ── NASI TELUR ────────────────────────────────────
            [$nastel, 'Nasi Telur Saus',      'nasi-telur-saus',       9000, 1000],
            [$nastel, 'Nasi Telur Kecap',     'nasi-telur-kecap',      8000, 1000],

            // ── AYAM GEPREK ───────────────────────────────────
            [$geprek, 'Nasi Ayam Geprek',     'nasi-ayam-geprek',     14000, 2000],
        ];

        foreach ($menus as [$catId, $name, $slug, $price, $cashback]) {
            Menu::create([
                'category_id'         => $catId,
                'name'                => $name,
                'slug'                => $slug,
                'description'         => null,
                'price'               => $price,
                'cashback'            => $cashback,
                'image'               => null,
                'is_available'        => true,
                'is_student_discount' => true,
                'student_price'       => $price - $cashback,
            ]);
        }
    }
}
