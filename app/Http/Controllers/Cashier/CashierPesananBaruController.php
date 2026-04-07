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
            $isBayarNanti = $request->payment_method === 'bayar_nanti';

            $order = Order::create([
                'cashier_id'     => auth()->id(),
                'order_type'     => 'cashier',
                'payment_method' => $request->payment_method,
                'customer_name'  => $request->customer_name,
                'status'         => Order::STATUS_DIPROSES,
                'is_paid'        => !$isBayarNanti,
            ]);

            $isMahasiswa = (bool) $request->input('is_mahasiswa', false);
            $total = 0;

            foreach ($request->items as $item) {
                $menu      = Menu::findOrFail($item['menu_id']);
                $cashback  = ($isMahasiswa && $menu->cashback > 0) ? $menu->cashback : 0;
                $unitPrice = $menu->price - $cashback;
                $subtotal  = $unitPrice * $item['quantity'];

                $order->items()->create([
                    'menu_id'    => $menu->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total_amount' => $total]);
        });

        return back()->with('success', 'Pesanan berhasil dibuat');
    }
}
