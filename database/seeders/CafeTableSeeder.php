<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use Illuminate\Database\Seeder;

class CafeTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($n = 1; $n <= 10; $n++) {
            CafeTable::create([
                'table_number' => $n,
                'qr_code'      => "http://localhost/order?table={$n}",
                'is_available' => true,
            ]);
        }
    }
}
