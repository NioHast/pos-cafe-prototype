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
            $this->upsertSystemUser(
                email: 'admin@example.com',
                name: 'Administrator',
                roleId: $adminRole->id,
            );
        }

        if ($cashierRole) {
            $this->upsertSystemUser(
                email: 'cashier@example.com',
                name: 'Kasir Demo',
                roleId: $cashierRole->id,
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
                $this->upsertSystemUser(
                    email: $student['email'],
                    name: $student['name'],
                    roleId: $studentRole->id,
                );
            }
        }
    }

    /**
     * Create or restore a seeded user account and keep key attributes in sync.
     */
    private function upsertSystemUser(string $email, string $name, int $roleId): void
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role_id' => $roleId,
                'is_active' => true,
            ]
        );

        if ($user->trashed()) {
            $user->restore();
        }
    }
}
