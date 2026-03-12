<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    public function riwayat(Request $request)
    {
        $customerId = auth()->id();

        $orders = $customerId
            ? Order::with(['items.menu', 'payment'])
                ->where('customer_id', $customerId)
                ->latest()
                ->get()
                ->map(fn($o) => [
                    'id'            => $o->id,
                    'order_code'    => $o->order_code,
                    'status'        => $o->status,
                    'total_amount'  => $o->total_amount,
                    'created_at'    => $o->created_at->toISOString(),
                    'items_summary' => $o->items
                        ->map(fn($i) => "{$i->quantity}x {$i->menu->name}")
                        ->join(', '),
                ])
            : collect();

        return Inertia::render('Customer/Riwayat/Index', compact('orders'));
    }

    public function store(Request $request)
    {
        // Akan diimplementasikan di fase order processing
        return back()->with('info', 'Fitur pemesanan segera hadir.');
    }

    public function show(Order $order)
    {
        // Placeholder — detail pesanan pelanggan
        return back();
    }

    public function status(string $code)
    {
        $order = Order::with(['items.menu', 'payment'])
            ->where('order_code', $code)
            ->firstOrFail();

        $data = [
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total_amount'   => $order->total_amount,
            'created_at'     => $order->created_at->toISOString(),
            'payment_method' => $order->payment?->payment_method,
            'items_summary'  => $order->items
                ->map(fn($i) => "{$i->quantity}x {$i->menu->name}")
                ->join(', '),
        ];

        return Inertia::render('Customer/Order/Status', ['order' => $data]);
    }
}
