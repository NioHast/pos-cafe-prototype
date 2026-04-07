<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Customer/Auth/Login');
    }

    public function login(CustomerLoginRequest $request)
    {
        // Cari akun yang sudah terdaftar
        $user = User::where('name', $request->name)
            ->where('role', 'customer')
            ->first();

        // Akun sudah ada — validasi NIM
        if ($user) {
            if ($user->nim !== $request->nim) {
                return back()->withErrors(['nim' => 'NIM tidak sesuai dengan nama yang terdaftar.']);
            }

            // Akun ditolak kasir
            if ($user->is_student_verified === false && $user->email !== null && str_contains($user->email, '@pending')) {
                // Cek apakah pernah ditolak (ditandai via email khusus)
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();
            return redirect()->route('customer.menu');
        }

        // Akun belum terdaftar → buat akun baru, menunggu verifikasi kasir
        $newUser = User::create([
            'name'                => $request->name,
            'email'               => strtolower(str_replace(' ', '.', $request->name)) . '.' . $request->nim . '@student.pending',
            'password'            => Hash::make($request->nim),
            'role'                => 'customer',
            'nim'                 => $request->nim,
            'is_student_verified' => null,
        ]);

        Auth::login($newUser, remember: true);
        $request->session()->regenerate();

        // Redirect ke menu dengan flash info menunggu verifikasi
        return redirect()->route('customer.menu')
            ->with('info', 'Akun Anda sedang menunggu verifikasi kasir. Diskon 10% akan aktif setelah disetujui.');
    }
}
