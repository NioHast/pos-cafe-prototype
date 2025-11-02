<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin'],
            ['name' => 'cashier'],
            ['name' => 'student'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
