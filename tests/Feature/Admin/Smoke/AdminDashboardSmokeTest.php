<?php

namespace Tests\Feature\Admin\Smoke;

use Tests\TestCase;

class AdminDashboardSmokeTest extends TestCase
{
    public function test_dashboard_route_path_is_admin(): void
    {
        $this->assertSame('/admin', parse_url(route('filament.admin.pages.dashboard'), PHP_URL_PATH));
    }

    public function test_dashboard_redirects_guest_to_filament_login(): void
    {
        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertRedirect(route('filament.admin.auth.login'));
    }
}
