<?php

namespace Tests\Feature\Admin;

use App\Models\CashierSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierSessionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_session_can_be_created_and_updated(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'shift_start' => now()->subHours(4),
            'shift_end' => null,
            'total_sales' => 100000,
            'total_transactions' => 10,
        ]);

        $this->assertDatabaseHas('cashier_sessions', [
            'id' => $session->id,
            'user_id' => $cashier->id,
            'total_transactions' => 10,
        ]);

        $session->update([
            'shift_end' => now(),
            'total_sales' => 150000,
            'total_transactions' => 15,
        ]);

        $session->refresh();

        $this->assertFalse($session->isActive());
        $this->assertDatabaseHas('cashier_sessions', [
            'id' => $session->id,
            'total_transactions' => 15,
        ]);
    }

    public function test_active_scope_and_duration_accessor_work_as_expected(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $start = now()->subHours(5)->startOfHour();
        $end = $start->copy()->addHours(3);

        CashierSession::create([
            'user_id' => $cashier->id,
            'shift_start' => $start,
            'shift_end' => $end,
            'total_sales' => 50000,
            'total_transactions' => 5,
        ]);

        CashierSession::create([
            'user_id' => $cashier->id,
            'shift_start' => now()->subHours(1),
            'shift_end' => null,
            'total_sales' => 80000,
            'total_transactions' => 8,
        ]);

        $activeCount = CashierSession::active()->count();
        $closedSession = CashierSession::whereNotNull('shift_end')->firstOrFail();

        $this->assertSame(1, $activeCount);
        $this->assertSame(3.0, (float) $closedSession->duration);
    }

    public function test_non_admin_cannot_access_cashier_session_admin_resource(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($cashier)
            ->get(route('filament.admin.resources.cashier-sessions.index'));

        $this->assertNotSame(200, $response->getStatusCode());
    }
}