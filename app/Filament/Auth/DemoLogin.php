<?php

namespace App\Filament\Auth;

use App\Models\User;
use App\Support\DemoAdminMode;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DemoLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        if (! DemoAdminMode::enabled()) {
            return parent::authenticate();
        }

        $data = $this->form->getState();
        $email = mb_strtolower((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if (($email !== 'admin@w9cafe.com') || ($password !== 'password')) {
            throw ValidationException::withMessages([
                'data.email' => 'Email atau password salah.',
            ]);
        }

        session()->put('demo_admin_authenticated', true);
        session()->regenerate();

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'name' => 'Admin Demo',
            'email' => 'admin@w9cafe.com',
            'role' => 'admin',
            'phone' => '-',
        ]);
        $user->exists = true;

        Auth::guard('admin')->setUser($user);

        return app(LoginResponse::class);
    }
}
