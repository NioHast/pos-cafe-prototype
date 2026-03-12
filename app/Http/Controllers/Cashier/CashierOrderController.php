<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Inertia\Inertia;

class CashierOrderController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['items.menu', 'cafeTable', 'payment', 'cashier']);

        return Inertia::render('Cashier/Order/Show', [
            'order' => [
                'id'             => $order->id,
                'order_code'     => $order->order_code,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'total_amount'   => $order->total_amount,
                'created_at'     => $order->created_at->toISOString(),
                'cashier_name'   => $order->cashier?->name,
                'table_number'   => $order->cafeTable?->table_number,
                'payment_method' => $order->payment?->payment_method,
                'payment_gateway'=> $order->payment?->payment_gateway,
                'items'          => $order->items->map(fn($i) => [
                    'id'         => $i->id,
                    'name'       => $i->menu->name,
                    'unit_price' => $i->unit_price,
                    'quantity'   => $i->quantity,
                    'subtotal'   => $i->subtotal,
                ]),
            ],
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $validTransitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready'],
            'ready'     => ['completed'],
        ];

        if (!in_array($request->status, $validTransitions[$order->status] ?? [])) {
            return back()->withErrors(['status' => 'Transisi status tidak valid.']);
        }

        $order->update(['status' => $request->status]);

        return redirect()->route('cashier.pesanan-aktif')
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
