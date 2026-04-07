<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'qris_image'],    ['value' => 'qris/qris-w9cafe.png']);
        Setting::updateOrCreate(['key' => 'qris_name'],     ['value' => 'W9 Cafe STIE Totalwin']);
        Setting::updateOrCreate(['key' => 'merchant_number'], ['value' => '00000000000000']);
    }
}
