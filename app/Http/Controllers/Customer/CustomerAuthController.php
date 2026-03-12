<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Customer/Auth/Login');
    }

    public function login(CustomerLoginRequest $request)
    {
        $user = User::where('name', $request->name)
            ->where('role', 'customer')
            ->first();

        if (!$user || $user->nim !== $request->nim) {
            return back()->withErrors(['nim' => 'Username atau NIM tidak valid.']);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('customer.menu');
    }
}
