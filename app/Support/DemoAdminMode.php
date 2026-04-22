<?php

namespace App\Support;

class DemoAdminMode
{
    public static function enabled(): bool
    {
        return (bool) config('demo.admin_only', false);
    }
}
