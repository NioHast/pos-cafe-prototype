<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\DemoAdminMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DemoAdminSessionAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! DemoAdminMode::enabled()) {
            return $next($request);
        }

        if ($request->session()->get('demo_admin_authenticated') !== true) {
            return $next($request);
        }

        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

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

        return $next($request);
    }
}
