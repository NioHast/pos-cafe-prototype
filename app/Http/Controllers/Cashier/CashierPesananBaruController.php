<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\OrderPromotionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashierPesananBaruController extends Controller
{
    public function index()
    {
        $categories = Category::with([
            'menus' => fn($q) => $q->where('is_available', true)->orderBy('name'),
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Cashier/PesananBaru', compact('categories'));
    }

    public function store(
        StoreOrderRequest $request,
        OrderPromotionService $orderPromotionService,
        InventoryService $inventoryService,
    )
    {
        DB::transaction(function () use ($request, $orderPromotionService, $inventoryService) {
            $isBayarNanti = $request->payment_method === 'bayar_nanti';
            $selectedPromotionIds = $request->input('promotion_ids', []);

            $order = Order::create([
                'cashier_id'     => Auth::id(),
                'order_type'     => 'cashier',
                'payment_method' => $request->payment_method,
                'customer_name'  => $request->customer_name,
                'status'         => Order::STATUS_DIPROSES,
                'is_paid'        => !$isBayarNanti,
            ]);

            $isMahasiswa = (bool) $request->input('is_mahasiswa', false);
            $total = 0;
            $appliedPromotions = [];

            foreach ($request->items as $item) {
                $menu      = Menu::findOrFail($item['menu_id']);

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

            $inventoryService->processSaleForOrder($order, Auth::id());
        });

        return back()->with('success', 'Pesanan berhasil dibuat');
    }
}
