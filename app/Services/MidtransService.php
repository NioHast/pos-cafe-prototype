<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function createSnapToken(Order $order, string $method): string
    {
        $order->load(['items.menu', 'cafeTable', 'customer']);

        $itemDetails = $order->items->map(fn($item) => [
            'id'       => (string) $item->menu_id,
            'price'    => (int) $item->unit_price,
            'quantity' => $item->quantity,
            'name'     => $item->menu->name,
        ])->toArray();

        $enabledPayments = match ($method) {
            'qris'     => ['qris'],
            'ewallet'  => ['gopay', 'shopeepay'],
            'transfer' => ['bank_transfer'],
            default    => ['qris', 'gopay', 'shopeepay', 'bank_transfer'],
        };

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->order_code,
                'gross_amount' => (int) $order->total_amount,
            ],
            'item_details'     => $itemDetails,
            'customer_details' => [
                'first_name' => $order->customer?->name ?? 'Customer',
            ],
            'enabled_payments' => $enabledPayments,
        ];

        $response = Http::withBasicAuth(config('midtrans.server_key'), '')
            ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);

        return $response->json('token') ?? '';
    }

    public function verifyNotification(array $payload): bool
    {
        $expected = hash('sha512',
            $payload['order_id'] .
            $payload['status_code'] .
            $payload['gross_amount'] .
            config('midtrans.server_key')
        );

        return $expected === ($payload['signature_key'] ?? '');
    }
}
