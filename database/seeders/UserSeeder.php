<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $baseUsers = [
            [
                'name' => 'Admin W9',
                'email' => 'admin@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => null,
            ],
            [
                'name' => 'Ahmad Kasir',
                'email' => 'kasir@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'phone' => null,
            ],
            [
                'name' => 'Budi Pelanggan',
                'email' => 'budi@customer.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => null,
            ],
        ];

        foreach ($baseUsers as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                ]
            );
        }

        for ($index = 1; $index <= 10; $index++) {
            $number = str_pad((string) $index, 2, '0', STR_PAD_LEFT);

            User::query()->updateOrCreate(
                ['email' => "kasir{$number}@w9cafe.com"],
                [
                    'name' => "Kasir {$number}",
                    'password' => Hash::make('password'),
                    'role' => 'cashier',
                    'phone' => '08' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
                ]
            );
        }
    }
}
