<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\CafeTable;
use App\Services\OrderPromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    public function store(Request $request, OrderPromotionService $orderPromotionService): JsonResponse
    {
        $request->validate([
            'customer_name'     => 'required|string|min:2|max:255',
            'customer_phone'    => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'table_id'          => 'required|integer|exists:cafe_tables,id',
            'is_mahasiswa'      => 'boolean',
            'promotion_ids'     => 'nullable|array',
            'promotion_ids.*'   => 'integer|exists:promotions,id',
            'items'             => 'required|array|min:1',
            'items.*.menu_id'   => 'required|integer|exists:menus,id',
            'items.*.quantity'  => 'required|integer|min:1|max:20',
        ], [
            'customer_name.required' => 'Nama wajib diisi.',
            'customer_phone.regex'   => 'Nomor telepon tidak valid.',
            'items.required'         => 'Pesanan tidak boleh kosong.',
            'items.min'              => 'Minimal 1 item dalam pesanan.',
        ]);

        return DB::transaction(function () use ($request, $orderPromotionService) {
            CafeTable::findOrFail($request->table_id);

            $isMahasiswa = (bool) $request->input('is_mahasiswa', false);
            $selectedPromotionIds = $request->input('promotion_ids', []);

            $order = Order::create([
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'table_id'       => $request->table_id,
                'cashier_id'     => null,
                'order_type'     => 'qr',
                'status'         => Order::STATUS_PENDING,
                'total_amount'   => 0,
            ]);

            $total = 0;
            $appliedPromotions = [];

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                if (!$menu->is_available) {
                    throw new Exception("Menu {$menu->name} tidak tersedia.");
                }

                $lineCalculation = $orderPromotionService->calculateLine(
                    $menu,
                    (int) $item['quantity'],
                    $isMahasiswa,
                    $selectedPromotionIds,
                );

                $order->items()->create([
                    'menu_id'    => $menu->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $lineCalculation['unit_price'],
                    'subtotal'   => $lineCalculation['subtotal'],
                ]);

                if ($lineCalculation['applied_promotion'] !== null) {
                    $appliedPromotions[] = $lineCalculation['applied_promotion'];
                }

                $total += $lineCalculation['subtotal'];
            }

            $order->update(['total_amount' => $total]);

            $orderPromotionService->persistOrderPromotions($order, $appliedPromotions);

            return response()->json([
                'order_code'   => $order->order_code,
                'total_amount' => $order->total_amount,
                'order_id'     => $order->id,
            ], 201);
        });
    }

    public function riwayat(Request $request)
    {
        // Riwayat berdasarkan nomor telepon di sessionStorage (dikirim via query param)
        $phone = $request->query('phone');

        $orders = $phone
            ? Order::with('items.menu')
                ->where('customer_phone', $phone)
                ->latest()
                ->get()
                ->map(fn($o) => [
                    'id'            => $o->id,
                    'order_code'    => $o->order_code,
                    'status'        => $o->status,
                    'total_amount'  => $o->total_amount,
                    'created_at'    => $o->created_at->toISOString(),
                    'payment_method' => $o->payment_method,
                    'customer_name'  => $o->customer_name,
                    'items_summary'  => $o->items
                        ->map(fn($i) => "{$i->quantity}x {$i->menu->name}")
                        ->join(', '),
                    'items' => $o->items->map(fn($i) => [
                        'name'     => $i->menu->name,
                        'quantity' => $i->quantity,
                        'subtotal' => (float) $i->subtotal,
                    ])->values()->toArray(),
                ])
            : collect();

        return Inertia::render('Customer/Riwayat/Index', compact('orders'));
    }

    public function status(string $code)
    {
        $order = Order::where('order_code', $code)->firstOrFail();

        return Inertia::render('Customer/Order/Status', [
            'order' => [
                'id'             => $order->id,
                'order_code'     => $order->order_code,
                'status'         => $order->status,
                'total_amount'   => $order->total_amount,
                'payment_method' => $order->payment_method,
                'created_at'     => $order->created_at->toISOString(),
            ],
        ]);
    }
}
