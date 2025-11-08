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
    }
}
