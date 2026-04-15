<?php

namespace Tests\Feature\Admin\Smoke;

use Tests\TestCase;

class AdminCurrentResourcesSmokeTest extends TestCase
{
    public function test_guest_is_redirected_to_login_for_current_admin_resource_index_pages(): void
    {
        $routes = [
            'filament.admin.resources.users.index',
            'filament.admin.resources.categories.index',
            'filament.admin.resources.menus.index',
            'filament.admin.resources.orders.index',
            'filament.admin.resources.ingredients.index',
            'filament.admin.resources.waste-records.index',
            'filament.admin.resources.stock-adjustments.index',
            'filament.admin.resources.cashier-sessions.index',
            'filament.admin.resources.promotions.index',
            'filament.admin.resources.incomes.index',
            'filament.admin.resources.expenses.index',
            'filament.admin.resources.receivables.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect(route('filament.admin.auth.login'));
        }
    }
}
