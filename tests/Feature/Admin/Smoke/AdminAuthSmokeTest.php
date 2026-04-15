<?php

namespace Tests\Feature\Admin\Smoke;

use Tests\TestCase;

class AdminAuthSmokeTest extends TestCase
{
    public function test_guest_is_redirected_to_filament_login_from_admin_root(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_filament_login_page_is_accessible_for_guest(): void
    {
        $response = $this->get(route('filament.admin.auth.login'));

        $response->assertOk();
    }

    public function test_dashboard_route_is_registered(): void
    {
        $this->assertSame('/admin', parse_url(route('filament.admin.pages.dashboard'), PHP_URL_PATH));
    }
}
