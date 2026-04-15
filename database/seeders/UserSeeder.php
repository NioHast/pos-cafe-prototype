<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'name' => 'Admin W9',
                'email' => 'admin@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ahmad Kasir',
                'email' => 'kasir@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Pelanggan',
                'email' => 'budi@customer.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
