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
                'name'                => 'Admin W9',
                'email'               => 'admin@w9cafe.com',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'nim'                 => null,
                'phone'               => null,
                'is_student_verified' => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'name'                => 'Ahmad Kasir',
                'email'               => 'kasir@w9cafe.com',
                'password'            => Hash::make('password'),
                'role'                => 'cashier',
                'nim'                 => null,
                'phone'               => null,
                'is_student_verified' => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'name'                => 'Budi Mahasiswa',
                'email'               => 'budi@student.com',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'nim'                 => '21120122140001',
                'phone'               => null,
                'is_student_verified' => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
