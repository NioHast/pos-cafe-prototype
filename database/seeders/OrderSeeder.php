<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CashierSession;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $admin = User::where('email', 'admin@example.com')->first();
        $cashier = User::where('email', 'cashier@example.com')->first();
        
        if (!$admin || !$cashier) {
            $this->command->warn('Users not found. Please run UserSeeder first.');
            return;
        }

        // Get menus
        $menus = Menu::all();
        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Please run DemoDataSeeder first.');
            return;
        }

        // Create cashier session
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'shift_start' => now()->startOfDay()->addHours(8),
            'shift_end' => now()->startOfDay()->addHours(16),
            'total_sales' => 0,
            'total_transactions' => 0,
        ]);

        $totalSales = 0;
        $totalTransactions = 0;

        // Create 30 orders for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $ordersPerDay = rand(3, 6);
            
            for ($j = 0; $j < $ordersPerDay; $j++) {
                $orderDate = Carbon::now()->subDays($i)->setTime(rand(9, 20), rand(0, 59));
                
                $order = Order::create([
                    'customer_id' => rand(0, 1) ? null : $admin->id,
                    'cashier_id' => $cashier->id,
                    'total_price' => 0, // Will calculate later
                    'payment_status' => 'paid',
                    'payment_method' => collect(['Cash', 'QRIS', 'Transfer', 'GoPay'])->random(),
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Add 1-4 items per order
                $itemsCount = rand(1, 4);
                $orderTotal = 0;

                for ($k = 0; $k < $itemsCount; $k++) {
                    $menu = $menus->random();
                    $quantity = rand(1, 3);
                    $subtotal = $menu->price * $quantity;
                    $orderTotal += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_id' => $menu->id,
                        'quantity' => $quantity,
                        'price_at_transaction' => $menu->price,
                        'handled_by' => $cashier->id,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }

                // Update order total
                $order->update(['total_price' => $orderTotal]);
                
                $totalSales += $orderTotal;
                $totalTransactions++;
            }
        }

        // Update session totals
        $session->update([
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
        ]);

        $this->command->info("✅ Created {$totalTransactions} orders with total sales: Rp " . number_format($totalSales, 0, ',', '.'));
    }
}
