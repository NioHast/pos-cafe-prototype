<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

class CashierDashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $totalPenjualan = Order::whereDate('created_at', $today)
            ->where('status', Order::STATUS_SELESAI)
            ->sum('total_amount') ?? 0;

        $jumlahTransaksi = Order::whereDate('created_at', $today)
            ->where('status', Order::STATUS_SELESAI)
            ->count();

        $pesananAktif = Order::where('status', '!=', Order::STATUS_SELESAI)
            ->where(function ($q) {
                $q->where('order_type', 'cashier')
                  ->orWhere(fn($q2) => $q2->where('order_type', 'qr')
                      ->where(fn($q3) =>
                          $q3->where('payment_method', 'cash')
                             ->orWhere(fn($q4) => $q4->where('payment_method', 'qris')->whereNotNull('payment_proof'))
                      )
                  );
            })->count();

        $cashPending = Order::where('status', Order::STATUS_PENDING)->where('payment_method', 'cash')->count();
        $qrisPending = Order::where('status', Order::STATUS_PENDING)->where('payment_method', 'qris')->whereNotNull('payment_proof')->count();

        $transaksiTerbaru = Order::with('items.menu')
            ->whereDate('created_at', $today)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_code'     => $o->order_code,
                'customer_name'  => $o->customer_name,
                'items_summary'  => $o->items->map(fn($i) => $i->quantity . 'x ' . $i->menu->name)->join(', '),
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment_method,
                'status'         => $o->status,
            ]);

        return Inertia::render('Cashier/Dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'pesananAktif',
            'cashPending',
            'qrisPending',
            'transaksiTerbaru'
        ));
    }
}
