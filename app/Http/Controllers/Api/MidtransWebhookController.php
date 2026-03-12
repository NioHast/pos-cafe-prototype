<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    public function handle(Request $request)
    {
        $payload = $request->all();

        if (!$this->midtrans->verifyNotification($payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $order = Order::where('order_code', $payload['order_id'])->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $payment = Payment::where('order_id', $order->id)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';

        if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus !== 'deny') {
            $payment->update([
                'status'  => 'success',
                'paid_at' => now(),
            ]);
            $order->update([
                'status'         => 'confirmed',
                'payment_status' => 'paid',
            ]);
        } elseif ($transactionStatus === 'pending') {
            $payment->update(['status' => 'pending']);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['ok' => true]);
    }
}
