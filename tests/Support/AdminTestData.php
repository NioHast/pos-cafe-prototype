<?php

namespace Tests\Support;

use App\Models\User;

class AdminTestData
{
    public static function makeAdmin(array $overrides = []): User
    {
        return new User(array_merge([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'role' => 'admin',
            'password' => 'password',
            'phone' => null,
        ], $overrides));
    }

    public static function makeCashier(array $overrides = []): User
    {
        return new User(array_merge([
            'name' => 'Cashier Test',
            'email' => 'cashier.test@example.com',
            'role' => 'cashier',
            'password' => 'password',
            'phone' => null,
        ], $overrides));
    }
}
