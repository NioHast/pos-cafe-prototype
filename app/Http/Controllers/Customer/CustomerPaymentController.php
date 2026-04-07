<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPaymentController extends Controller
{
    public function showChoose(string $orderCode): Response
    {
        $order = Order::with(['items.menu', 'cafeTable'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        if ($order->status !== Order::STATUS_PENDING) {
            return Inertia::location('/customer/riwayat');
        }

        return Inertia::render('Customer/Payment/Choose', [
            'order'        => $order->only(['id', 'order_code', 'total_amount', 'customer_name']),
            'items'        => $order->items->map(fn($i) => [
                'name'     => $i->menu->name,
                'qty'      => $i->quantity,
                'subtotal' => $i->subtotal,
            ]),
            'table_number' => $order->cafeTable?->table_number,
        ]);
    }

    public function chooseCash(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $order->update(['payment_method' => 'cash']);
        return response()->json(['message' => 'ok', 'order_code' => $order->order_code]);
    }

    public function chooseQris(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $order->update(['payment_method' => 'qris']);
        return response()->json([
            'qris_image'   => asset('storage/' . Setting::get('qris_image', 'qris/qris-w9cafe.png')),
            'qris_name'    => Setting::get('qris_name', 'W9 Cafe'),
            'total_amount' => $order->total_amount,
            'order_code'   => $order->order_code,
        ]);
    }

    public function showCashStatus(string $orderCode): RedirectResponse
    {
        return redirect('/customer/riwayat');
    }

    public function showQrisUpload(string $orderCode): Response
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if (in_array($order->status, [Order::STATUS_DIPROSES, Order::STATUS_SELESAI])) {
            return Inertia::render('Customer/Payment/QrisStatus', ['order' => $this->orderData($order)]);
        }

        // rejection_note is set when cashier rejects and clears proof, allowing re-upload
        $rejectedMessage = ($order->payment_method === 'qris' && $order->rejection_note && !$order->payment_proof)
            ? $order->rejection_note
            : null;

        return Inertia::render('Customer/Payment/QrisUpload', [
            'order'           => $order->only(['id', 'order_code', 'total_amount']),
            'qrisImage'       => asset('storage/' . Setting::get('qris_image', 'qris/qris-w9cafe.png')),
            'qrisName'        => Setting::get('qris_name', 'W9 Cafe'),
            'totalAmount'     => $order->total_amount,
            'rejectedMessage' => $rejectedMessage,
        ]);
    }

    public function uploadQrisProof(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof')->store('proofs', 'public');

        $order->update([
            'payment_proof'  => $path,
            'payment_method' => 'qris',
            'rejection_note' => null,
        ]);

        return response()->json(['message' => 'Bukti berhasil dikirim']);
    }

    public function showQrisStatus(string $orderCode): Response
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();
        return Inertia::render('Customer/Payment/QrisStatus', ['order' => $this->orderData($order)]);
    }

    private function orderData(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'total_amount'   => $order->total_amount,
            'payment_method' => $order->payment_method,
            'rejection_note' => $order->rejection_note,
        ];
    }
}
