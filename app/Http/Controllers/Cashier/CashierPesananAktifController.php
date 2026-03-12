<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

class CashierPesananAktifController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.menu', 'cafeTable', 'payment'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        $counts = [
            'all'     => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'paid'    => $orders->where('payment_status', 'paid')->count(),
            'selesai' => $orders->where('status', 'ready')->count(),
        ];

        $ordersData = $orders->map(fn($o) => [
            'id'             => $o->id,
            'order_code'     => $o->order_code,
            'status'         => $o->status,
            'payment_status' => $o->payment_status,
            'created_at'     => $o->created_at->toISOString(),
            'items_summary'  => $o->items->map(fn($i) => $i->quantity . 'x ' . $i->menu->name)->join(', '),
            'total_amount'   => $o->total_amount,
        ]);

        return Inertia::render('Cashier/PesananAktif', [
            'orders' => $ordersData,
            'counts' => $counts,
        ]);
    }
}
