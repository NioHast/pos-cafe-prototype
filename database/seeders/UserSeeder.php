<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        if ($adminRole) {
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('password'),
                    'role_id' => $adminRole->id,
                ]
            );
        }

        if ($cashierRole) {
            // Create cashier user
            User::firstOrCreate(
                ['email' => 'cashier@example.com'],
                [
                    'name' => 'Kasir Demo',
                    'password' => Hash::make('password'),
                    'role_id' => $cashierRole->id,
                ]
            );
        }

        $studentRole = Role::where('name', 'student')->first();

        if ($studentRole) {
            $students = [
                ['name' => 'Andi Pratama', 'email' => 'andi@student.example.com'],
                ['name' => 'Siti Rahayu', 'email' => 'siti@student.example.com'],
                ['name' => 'Budi Santoso', 'email' => 'budi@student.example.com'],
            ];

            foreach ($students as $student) {
                User::firstOrCreate(
                    ['email' => $student['email']],
                    [
                        'name' => $student['name'],
                        'password' => Hash::make('password'),
                        'role_id' => $studentRole->id,
                    ]
                );
            }
        }
    }
}
