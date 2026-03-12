<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerPaymentController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    public function initiate(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:qris,ewallet,cash,transfer',
        ]);

        $token = $this->midtrans->createSnapToken($order, $request->payment_method);

        // Buat atau update payment record
        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_method'  => $request->payment_method,
                'payment_gateway' => 'midtrans',
                'amount'          => $order->total_amount,
                'status'          => 'pending',
            ]
        );

        return Inertia::render('Customer/Payment/Process', [
            'snapToken' => $token,
            'order'     => $order->only('id', 'order_code', 'total_amount'),
            'clientKey' => config('midtrans.client_key'),
            'snapUrl'   => config('midtrans.snap_url'),
        ]);
    }
}
