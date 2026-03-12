<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashierRiwayatController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.menu', 'payment', 'cashier'])
            ->where('status', 'completed')
            ->when($request->search, fn($q) => $q->where('order_code', 'like', '%' . $request->search . '%'))
            ->when($request->date,   fn($q) => $q->whereDate('created_at', $request->date))
            ->when($request->method, fn($q) => $q->whereHas('payment', fn($p) => $p->where('payment_method', $request->method)))
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_code'     => $o->order_code,
                'created_at'     => $o->created_at->toISOString(),
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment?->payment_method,
                'cashier_name'   => $o->cashier?->name,
                'status'         => $o->status,
                'payment_status' => $o->payment_status,
            ]);

        return Inertia::render('Cashier/RiwayatPesanan', [
            'orders'  => $orders,
            'filters' => $request->only(['search', 'date', 'method']),
        ]);
    }
}
