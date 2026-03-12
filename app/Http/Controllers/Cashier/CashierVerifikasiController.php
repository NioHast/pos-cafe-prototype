<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class CashierVerifikasiController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'customer')->latest()->get();

        $counts = [
            'menunggu'  => $students->whereNull('is_student_verified')->count()
                         + $students->where('is_student_verified', false)->count(),
            'disetujui' => $students->where('is_student_verified', true)->count(),
        ];

        $studentsData = $students->map(fn($s) => [
            'id'                   => $s->id,
            'name'                 => $s->name,
            'nim'                  => $s->nim,
            'is_student_verified'  => $s->is_student_verified,
            'created_at'           => $s->created_at->toISOString(),
        ]);

        return Inertia::render('Cashier/VerifikasiAkun', [
            'students' => $studentsData,
            'counts'   => $counts,
        ]);
    }

    public function approve(User $user)
    {
        $user->update(['is_student_verified' => true]);
        return back()->with('success', 'Akun mahasiswa berhasil disetujui.');
    }

    public function reject(User $user)
    {
        $user->update(['is_student_verified' => false]);
        return back()->with('success', 'Akun mahasiswa ditolak.');
    }
}
