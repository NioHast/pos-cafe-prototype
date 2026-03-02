<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class StudentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentRole = Role::where('name', 'student')->first();

        if (! $studentRole) {
            return;
        }

        $profiles = [
            [
                'email' => 'andi@student.example.com',
                'nim' => '2024001',
                'faculty' => 'Fakultas Teknik',
                'major' => 'Teknik Informatika',
                'year' => 2024,
            ],
            [
                'email' => 'siti@student.example.com',
                'nim' => '2024002',
                'faculty' => 'Fakultas Ekonomi',
                'major' => 'Manajemen',
                'year' => 2024,
            ],
            [
                'email' => 'budi@student.example.com',
                'nim' => '2023015',
                'faculty' => 'Fakultas Teknik',
                'major' => 'Sistem Informasi',
                'year' => 2023,
            ],
        ];

        foreach ($profiles as $profile) {
            $user = User::where('email', $profile['email'])->first();

            if ($user) {
                StudentProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nim' => $profile['nim'],
                        'faculty' => $profile['faculty'],
                        'major' => $profile['major'],
                        'year' => $profile['year'],
                    ]
                );
            }
        }
    }
}
