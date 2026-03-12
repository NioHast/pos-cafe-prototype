<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Inertia\Inertia;

class CashierDashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $totalPenjualan = Payment::whereDate('created_at', $today)
            ->where('status', 'success')
            ->sum('amount') ?? 0;

        $jumlahTransaksi = Order::whereDate('created_at', $today)
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        $pesananAktif = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $transaksiTerbaru = Order::with(['items.menu', 'payment'])
            ->whereDate('created_at', $today)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_code'     => $o->order_code,
                'items_summary'  => $o->items->map(fn($i) => $i->quantity . 'x ' . $i->menu->name)->join(', '),
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment?->payment_method,
                'status'         => $o->status,
            ]);

        return Inertia::render('Cashier/Dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'pesananAktif',
            'transaksiTerbaru'
        ));
    }
}
