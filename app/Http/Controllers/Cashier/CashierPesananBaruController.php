<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashierPesananBaruController extends Controller
{
    public function index()
    {
        $categories = Category::with([
            'menus' => fn($q) => $q->where('is_available', true)->orderBy('name'),
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Cashier/PesananBaru', compact('categories'));
    }

    public function store(StoreOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $order = Order::create([
                'cashier_id' => auth()->id(),
                'order_type' => 'cashier',
                'status'     => 'confirmed',
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $menu     = Menu::findOrFail($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];

                $order->items()->create([
                    'menu_id'    => $menu->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $menu->price,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total_amount' => $total]);

            if ($request->payment_method) {
                $order->payment()->create([
                    'payment_method'  => $request->payment_method,
                    'payment_gateway' => 'manual',
                    'amount'          => $total,
                    'status'          => 'success',
                    'paid_at'         => now(),
                ]);

                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'completed',
                ]);
            }

            return $order;
        });

        return back()->with('success', 'Pesanan berhasil dibuat');
    }
}
