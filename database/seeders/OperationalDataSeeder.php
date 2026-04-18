<?php

namespace Database\Seeders;

use App\Models\AppliedPromotion;
use App\Models\CafeTable;
use App\Models\CashierSession;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Receivable;
use App\Models\User;
use App\Models\WasteRecord;
use App\Services\InventoryService;
use App\Services\StockReconciliationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperationalDataSeeder extends Seeder
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected StockReconciliationService $stockReconciliationService,
    ) {
    }

    public function run(): void
    {
        $this->truncateOperationalTables();

        $this->call([
            CategorySeeder::class,
            MenuSeeder::class,
            CafeTableSeeder::class,
            SettingSeeder::class,
        ]);

        $ingredients = $this->seedIngredientsAndBatches();
        $this->seedMenuIngredients($ingredients);

        $promotions = $this->seedPromotions();
        $this->seedOrdersAndTransactions($promotions, 360);

        $this->seedCashierSessions();
        $this->seedFinancialData();
        $this->seedAdjustmentsAndWaste();
    }

    private function truncateOperationalTables(): void
    {
        $tables = [
            'applied_promotions',
            'promotion_rules',
            'promotions',
            'daily_ingredient_usages',
            'stock_movements',
            'stock_adjustments',
            'waste_records',
            'menu_ingredients',
            'ingredient_batches',
            'ingredients',
            'payments',
            'order_items',
            'orders',
            'incomes',
            'expenses',
            'receivables',
            'cashier_sessions',
            'cafe_tables',
            'menus',
            'categories',
            'settings',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $quotedTables = collect($tables)
                ->map(fn (string $table): string => '"' . $table . '"')
                ->implode(', ');

            DB::statement("TRUNCATE TABLE {$quotedTables} RESTART IDENTITY CASCADE");

            return;
        }

        DB::connection()->getSchemaBuilder()->disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            DB::connection()->getSchemaBuilder()->enableForeignKeyConstraints();
        }
    }

    private function seedIngredientsAndBatches(): array
    {
        $definitions = [
            'robusta_beans' => ['name' => 'Biji Kopi Robusta', 'unit' => 'gram', 'threshold' => 3000, 'opening_stock' => 250000, 'cost' => 0.85],
            'fresh_milk' => ['name' => 'Susu Fresh', 'unit' => 'ml', 'threshold' => 5000, 'opening_stock' => 120000, 'cost' => 0.012],
            'condensed_milk' => ['name' => 'Susu Kental Manis', 'unit' => 'ml', 'threshold' => 3000, 'opening_stock' => 60000, 'cost' => 0.02],
            'sugar_syrup' => ['name' => 'Simple Syrup', 'unit' => 'ml', 'threshold' => 5000, 'opening_stock' => 100000, 'cost' => 0.005],
            'tea_leaves' => ['name' => 'Daun Teh', 'unit' => 'gram', 'threshold' => 2000, 'opening_stock' => 80000, 'cost' => 0.35],
            'lime_juice' => ['name' => 'Perasan Jeruk Nipis', 'unit' => 'ml', 'threshold' => 3000, 'opening_stock' => 50000, 'cost' => 0.01],
            'cocoa_powder' => ['name' => 'Bubuk Coklat', 'unit' => 'gram', 'threshold' => 1500, 'opening_stock' => 30000, 'cost' => 0.45],
            'matcha_powder' => ['name' => 'Bubuk Matcha', 'unit' => 'gram', 'threshold' => 1000, 'opening_stock' => 20000, 'cost' => 0.7],
            'water' => ['name' => 'Air Mineral Curah', 'unit' => 'ml', 'threshold' => 10000, 'opening_stock' => 300000, 'cost' => 0.002],
            'sereh_jahe_bottle' => ['name' => 'Sereh Jahe Botol', 'unit' => 'pcs', 'threshold' => 100, 'opening_stock' => 1500, 'cost' => 4500],
            'kopi_susu_bottle' => ['name' => 'Kopi Susu Botol Siap Jual', 'unit' => 'pcs', 'threshold' => 120, 'opening_stock' => 2000, 'cost' => 6500],
            'fruit_tea_bottle' => ['name' => 'Fruit Tea Botol', 'unit' => 'pcs', 'threshold' => 120, 'opening_stock' => 2500, 'cost' => 3200],
            'mineral_water_bottle' => ['name' => 'Air Mineral Botol', 'unit' => 'pcs', 'threshold' => 200, 'opening_stock' => 3000, 'cost' => 2800],
            'banana' => ['name' => 'Pisang', 'unit' => 'pcs', 'threshold' => 150, 'opening_stock' => 2000, 'cost' => 1500],
            'chocolate_sauce' => ['name' => 'Saus Coklat', 'unit' => 'ml', 'threshold' => 1000, 'opening_stock' => 25000, 'cost' => 0.03],
            'grated_cheese' => ['name' => 'Keju Parut', 'unit' => 'gram', 'threshold' => 1200, 'opening_stock' => 30000, 'cost' => 0.12],
            'tempe' => ['name' => 'Tempe', 'unit' => 'pcs', 'threshold' => 120, 'opening_stock' => 1800, 'cost' => 900],
            'potato_frozen' => ['name' => 'Kentang Frozen', 'unit' => 'gram', 'threshold' => 5000, 'opening_stock' => 150000, 'cost' => 0.02],
            'cooking_oil' => ['name' => 'Minyak Goreng', 'unit' => 'ml', 'threshold' => 5000, 'opening_stock' => 120000, 'cost' => 0.006],
            'noodle_pack' => ['name' => 'Mie Instan', 'unit' => 'pcs', 'threshold' => 300, 'opening_stock' => 5000, 'cost' => 2200],
            'egg' => ['name' => 'Telur Ayam', 'unit' => 'pcs', 'threshold' => 500, 'opening_stock' => 8000, 'cost' => 1800],
            'rice_raw' => ['name' => 'Beras', 'unit' => 'gram', 'threshold' => 15000, 'opening_stock' => 500000, 'cost' => 0.015],
            'chicken_meat' => ['name' => 'Daging Ayam', 'unit' => 'gram', 'threshold' => 4000, 'opening_stock' => 120000, 'cost' => 0.05],
            'shrimp' => ['name' => 'Udang', 'unit' => 'gram', 'threshold' => 2000, 'opening_stock' => 80000, 'cost' => 0.09],
            'soy_sauce' => ['name' => 'Kecap Manis', 'unit' => 'ml', 'threshold' => 1200, 'opening_stock' => 30000, 'cost' => 0.01],
            'garlic' => ['name' => 'Bawang Putih', 'unit' => 'gram', 'threshold' => 900, 'opening_stock' => 20000, 'cost' => 0.04],
            'shallot' => ['name' => 'Bawang Merah', 'unit' => 'gram', 'threshold' => 900, 'opening_stock' => 20000, 'cost' => 0.04],
            'chili_paste' => ['name' => 'Cabai Giling', 'unit' => 'gram', 'threshold' => 700, 'opening_stock' => 15000, 'cost' => 0.05],
        ];

        $ingredientMap = [];

        foreach ($definitions as $key => $definition) {
            $ingredient = Ingredient::query()->create([
                'name' => $definition['name'],
                'unit' => $definition['unit'],
                'low_stock_threshold' => $definition['threshold'],
                'is_active' => true,
            ]);

            $ingredientMap[$key] = $ingredient->id;

            $firstBatchQty = round($definition['opening_stock'] * 0.65, 2);
            $secondBatchQty = round($definition['opening_stock'] * 0.35, 2);

            IngredientBatch::query()->create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $firstBatchQty,
                'expiry_date' => now()->addDays(random_int(45, 120))->toDateString(),
                'received_at' => now()->subDays(random_int(20, 45)),
                'cost_per_unit' => $definition['cost'],
            ]);

            IngredientBatch::query()->create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $secondBatchQty,
                'expiry_date' => now()->addDays(random_int(121, 240))->toDateString(),
                'received_at' => now()->subDays(random_int(1, 19)),
                'cost_per_unit' => round($definition['cost'] * 1.05, 2),
            ]);
        }

        return $ingredientMap;
    }

    private function seedMenuIngredients(array $ingredientMap): void
    {
        $recipes = [
            'Espresso' => ['robusta_beans' => 18],
            'Americano' => ['robusta_beans' => 18, 'water' => 120],
            'Es Americano' => ['robusta_beans' => 18, 'water' => 180],
            'Kopi Susu Panas' => ['robusta_beans' => 16, 'fresh_milk' => 120, 'condensed_milk' => 20, 'sugar_syrup' => 10],
            'Es Kopi Susu' => ['robusta_beans' => 16, 'fresh_milk' => 140, 'condensed_milk' => 20, 'sugar_syrup' => 10],

            'Teh Tawar Panas' => ['tea_leaves' => 5, 'water' => 220],
            'Teh Manis Panas' => ['tea_leaves' => 5, 'water' => 220, 'sugar_syrup' => 20],
            'Es Teh Tawar' => ['tea_leaves' => 5, 'water' => 250],
            'Es Teh Manis' => ['tea_leaves' => 5, 'water' => 250, 'sugar_syrup' => 20],
            'Teh Susu Manis Panas' => ['tea_leaves' => 4, 'fresh_milk' => 120, 'condensed_milk' => 20, 'sugar_syrup' => 5],
            'Es Teh Susu Manis' => ['tea_leaves' => 4, 'fresh_milk' => 130, 'condensed_milk' => 20, 'sugar_syrup' => 8],

            'Jeruk Nipis Panas' => ['lime_juice' => 35, 'water' => 200, 'sugar_syrup' => 10],
            'Es Jeruk Nipis' => ['lime_juice' => 35, 'water' => 220, 'sugar_syrup' => 15],
            'Lime Tea Panas' => ['tea_leaves' => 4, 'lime_juice' => 20, 'water' => 200, 'sugar_syrup' => 10],
            'Es Lime Tea' => ['tea_leaves' => 4, 'lime_juice' => 20, 'water' => 220, 'sugar_syrup' => 12],

            'Coklat Panas' => ['cocoa_powder' => 15, 'fresh_milk' => 120, 'sugar_syrup' => 15],
            'Es Coklat' => ['cocoa_powder' => 15, 'fresh_milk' => 130, 'sugar_syrup' => 15],
            'Matcha Panas' => ['matcha_powder' => 8, 'fresh_milk' => 120, 'sugar_syrup' => 15],
            'Es Matcha' => ['matcha_powder' => 8, 'fresh_milk' => 130, 'sugar_syrup' => 15],

            'Sereh Jahe Botol' => ['sereh_jahe_bottle' => 1],
            'Kopi Susu Botol' => ['kopi_susu_bottle' => 1],
            'Fruit Tea' => ['fruit_tea_bottle' => 1],
            'Air Mineral' => ['mineral_water_bottle' => 1],

            'Pisang Coklat Keju' => ['banana' => 1, 'chocolate_sauce' => 20, 'grated_cheese' => 15, 'cooking_oil' => 15],
            'Tempe Mendoan' => ['tempe' => 2, 'cooking_oil' => 20],
            'Kentang (French Fries)' => ['potato_frozen' => 180, 'cooking_oil' => 30],

            'Mie Goreng Telur' => ['noodle_pack' => 1, 'egg' => 1, 'soy_sauce' => 15, 'garlic' => 5, 'shallot' => 5, 'chili_paste' => 3, 'cooking_oil' => 10],
            'Mie Rebus Telur' => ['noodle_pack' => 1, 'egg' => 1, 'garlic' => 5, 'shallot' => 5, 'chili_paste' => 3],

            'Nasgor Telur' => ['rice_raw' => 180, 'egg' => 1, 'soy_sauce' => 20, 'garlic' => 6, 'shallot' => 6, 'chili_paste' => 4, 'cooking_oil' => 12],
            'Nasgor Ayam + Telur' => ['rice_raw' => 180, 'chicken_meat' => 60, 'egg' => 1, 'soy_sauce' => 20, 'garlic' => 6, 'shallot' => 6, 'chili_paste' => 4, 'cooking_oil' => 12],
            'Nasgor Udang + Telur' => ['rice_raw' => 180, 'shrimp' => 70, 'egg' => 1, 'soy_sauce' => 20, 'garlic' => 6, 'shallot' => 6, 'chili_paste' => 4, 'cooking_oil' => 12],

            'Nasi Telur + Teh/Es Teh' => ['rice_raw' => 180, 'egg' => 2, 'soy_sauce' => 15, 'garlic' => 4, 'shallot' => 4, 'cooking_oil' => 10, 'tea_leaves' => 3, 'sugar_syrup' => 8],
        ];

        foreach ($recipes as $menuName => $ingredients) {
            $menu = Menu::query()->where('name', $menuName)->first();

            if (! $menu) {
                continue;
            }

            foreach ($ingredients as $ingredientKey => $quantityUsed) {
                if (! isset($ingredientMap[$ingredientKey])) {
                    continue;
                }

                MenuIngredient::query()->updateOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'ingredient_id' => $ingredientMap[$ingredientKey],
                    ],
                    [
                        'quantity_used' => $quantityUsed,
                    ]
                );
            }
        }
    }

    private function seedPromotions(): Collection
    {
        $kopiCategoryId = Category::query()->where('slug', 'kopi-robusta')->value('id');
        $cemilanCategoryId = Category::query()->where('slug', 'cemilan')->value('id');
        $esKopiSusuId = Menu::query()->where('slug', 'es-kopi-susu')->value('id');

        $promoCoffee = Promotion::query()->create([
            'name' => 'Promo Kopi Pagi 10%',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'min_purchase' => 15000,
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'applicable_items' => null,
            'description' => 'Diskon pagi untuk kategori kopi.',
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        if ($kopiCategoryId) {
            $promoCoffee->rules()->create([
                'applicable_type' => 'category',
                'applicable_id' => $kopiCategoryId,
            ]);
        }

        $promoSnack = Promotion::query()->create([
            'name' => 'Promo Cemilan Hemat 2K',
            'type' => Promotion::TYPE_FIXED_AMOUNT,
            'discount_value' => 2000,
            'min_purchase' => 10000,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(45)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'applicable_items' => null,
            'description' => 'Potongan harga untuk cemilan.',
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        if ($cemilanCategoryId) {
            $promoSnack->rules()->create([
                'applicable_type' => 'category',
                'applicable_id' => $cemilanCategoryId,
            ]);
        }

        $promoMenu = Promotion::query()->create([
            'name' => 'Promo Es Kopi Susu 3K',
            'type' => Promotion::TYPE_FIXED_AMOUNT,
            'discount_value' => 3000,
            'min_purchase' => null,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'applicable_items' => null,
            'description' => 'Diskon menu favorit.',
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        if ($esKopiSusuId) {
            $promoMenu->rules()->create([
                'applicable_type' => 'menu',
                'applicable_id' => $esKopiSusuId,
            ]);
        }

        Promotion::query()->create([
            'name' => 'Promo Bundle Weekend (Draft)',
            'type' => Promotion::TYPE_BUNDLE,
            'discount_value' => 5000,
            'min_purchase' => 25000,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'status' => Promotion::STATUS_SCHEDULED,
            'applicable_items' => null,
            'description' => 'Promo terjadwal untuk akhir pekan.',
            'usage_limit' => 500,
            'usage_count' => 0,
        ]);

        return Promotion::query()
            ->whereIn('status', [Promotion::STATUS_ACTIVE, Promotion::STATUS_SCHEDULED])
            ->with('rules')
            ->get();
    }

    private function seedOrdersAndTransactions(Collection $promotions, int $orderCount): void
    {
        $faker = fake('id_ID');
        $menus = Menu::query()->where('is_available', true)->with('category')->get();
        $tableIds = CafeTable::query()->pluck('id')->values();
        $cashierIds = User::query()->where('role', 'cashier')->pluck('id')->values();

        if ($menus->isEmpty() || $cashierIds->isEmpty()) {
            return;
        }

        for ($index = 0; $index < $orderCount; $index++) {
            $createdAt = Carbon::now()
                ->subDays(random_int(0, 90))
                ->setTime(random_int(8, 22), random_int(0, 59), random_int(0, 59));

            $orderType = $faker->boolean(60) ? 'qr' : 'cashier';
            $paymentMethod = $this->pickPaymentMethod($orderType);
            $status = $this->pickOrderStatus($paymentMethod);
            $isPaid = $this->determineIsPaid($status, $paymentMethod);
            $isStudent = $faker->boolean(35);

            $order = new Order([
                'order_code' => sprintf('ORD-%s-%05d', $createdAt->format('Ymd'), $index + 1),
                'customer_name' => $faker->name(),
                'customer_phone' => '08' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'table_id' => $orderType === 'qr' && $tableIds->isNotEmpty() ? $tableIds->random() : null,
                'cashier_id' => $cashierIds->random(),
                'status' => $status,
                'order_type' => $orderType,
                'payment_method' => $paymentMethod,
                'payment_proof' => $paymentMethod === 'qris' ? 'payments/sample-qris-proof.jpg' : null,
                'rejection_note' => null,
                'is_paid' => $isPaid,
                'total_amount' => 0,
                'notes' => $faker->boolean(20) ? $faker->sentence(6) : null,
            ]);
            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;
            $order->save();

            $itemCount = random_int(1, 4);
            $selectedMenus = $menus->random(min($itemCount, $menus->count()));

            if ($selectedMenus instanceof Menu) {
                $selectedMenus = collect([$selectedMenus]);
            }

            $grossTotal = 0.0;

            foreach ($selectedMenus as $menu) {
                $quantity = random_int(1, 3);
                $unitPrice = ($isStudent && $menu->is_student_discount && $menu->student_price !== null)
                    ? (float) $menu->student_price
                    : (float) $menu->price;

                $subtotal = $unitPrice * $quantity;
                $grossTotal += $subtotal;

                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => null,
                ]);
                $orderItem->created_at = $createdAt;
                $orderItem->updated_at = $createdAt;
                $orderItem->save();
            }

            $discount = $this->applyRandomPromotion($promotions, $selectedMenus, $grossTotal, $order, $createdAt);
            $netTotal = max(0, $grossTotal - $discount);

            $order->forceFill([
                'total_amount' => $netTotal,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->saveQuietly();

            if ($isPaid) {
                $paymentMethodForPayment = match ($paymentMethod) {
                    'qris' => 'qris',
                    'cash' => 'cash',
                    default => 'transfer',
                };

                $payment = new Payment([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethodForPayment,
                    'payment_gateway' => $paymentMethodForPayment === 'qris' ? 'midtrans' : 'manual',
                    'transaction_id' => strtoupper('TXN-' . $createdAt->format('YmdHis') . '-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)),
                    'amount' => $netTotal,
                    'status' => 'success',
                    'paid_at' => $createdAt->copy()->addMinutes(random_int(5, 180)),
                ]);
                $payment->created_at = $createdAt;
                $payment->updated_at = $createdAt;
                $payment->save();
            }

            if (in_array($status, [Order::STATUS_DIPROSES, Order::STATUS_SELESAI], true)) {
                $this->inventoryService->processSaleForOrder($order->fresh('items'), $order->cashier_id);
            }
        }
    }

    private function pickPaymentMethod(string $orderType): string
    {
        $random = random_int(1, 100);

        if ($orderType === 'qr') {
            return match (true) {
                $random <= 80 => 'qris',
                $random <= 95 => 'cash',
                default => 'bayar_nanti',
            };
        }

        return match (true) {
            $random <= 70 => 'cash',
            $random <= 90 => 'qris',
            default => 'bayar_nanti',
        };
    }

    private function pickOrderStatus(string $paymentMethod): string
    {
        $random = random_int(1, 100);

        if ($paymentMethod === 'bayar_nanti') {
            return match (true) {
                $random <= 45 => Order::STATUS_PENDING,
                $random <= 80 => Order::STATUS_DIPROSES,
                default => Order::STATUS_SELESAI,
            };
        }

        return match (true) {
            $random <= 20 => Order::STATUS_PENDING,
            $random <= 60 => Order::STATUS_DIPROSES,
            default => Order::STATUS_SELESAI,
        };
    }

    private function determineIsPaid(string $status, string $paymentMethod): bool
    {
        if ($status === Order::STATUS_PENDING) {
            return false;
        }

        if ($paymentMethod === 'bayar_nanti') {
            return random_int(1, 100) <= 15;
        }

        return random_int(1, 100) <= 95;
    }

    private function applyRandomPromotion(
        Collection $promotions,
        Collection $selectedMenus,
        float $grossTotal,
        Order $order,
        Carbon $createdAt
    ): float {
        if ($grossTotal < 10000 || random_int(1, 100) > 35) {
            return 0;
        }

        $applicablePromotions = $promotions
            ->filter(function (Promotion $promotion) use ($selectedMenus, $grossTotal): bool {
                if (! $promotion->canBeUsed()) {
                    return false;
                }

                if ($promotion->min_purchase !== null && $grossTotal < (float) $promotion->min_purchase) {
                    return false;
                }

                return $selectedMenus->contains(function (Menu $menu) use ($promotion): bool {
                    return $promotion->isApplicableTo($menu->id, $menu->category_id);
                });
            })
            ->values();

        if ($applicablePromotions->isEmpty()) {
            return 0;
        }

        /** @var Promotion $promotion */
        $promotion = $applicablePromotions->random();

        $discount = $promotion->type === Promotion::TYPE_PERCENTAGE
            ? round($grossTotal * ((float) $promotion->discount_value / 100), 2)
            : min($grossTotal, (float) $promotion->discount_value);

        if ($discount <= 0) {
            return 0;
        }

        $appliedPromotion = new AppliedPromotion([
            'order_id' => $order->id,
            'promotion_id' => $promotion->id,
            'discount_type' => $promotion->type,
            'discount_value' => (float) $promotion->discount_value,
            'discount_amount' => $discount,
        ]);
        $appliedPromotion->created_at = $createdAt;
        $appliedPromotion->updated_at = $createdAt;
        $appliedPromotion->save();

        $promotion->increment('usage_count');

        return $discount;
    }

    private function seedCashierSessions(): void
    {
        $dailyRecap = Order::query()
            ->whereNotNull('cashier_id')
            ->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_SELESAI])
            ->selectRaw('cashier_id, DATE(created_at) as tx_date, COUNT(*) as total_transactions, COALESCE(SUM(total_amount), 0) as total_sales')
            ->groupBy('cashier_id', DB::raw('DATE(created_at)'))
            ->orderBy('tx_date')
            ->get();

        foreach ($dailyRecap as $row) {
            if (random_int(1, 100) > 70) {
                continue;
            }

            $shiftStart = Carbon::parse($row->tx_date)->setTime(random_int(7, 10), random_int(0, 59));
            $shiftEnd = $shiftStart->copy()->addHours(random_int(6, 10));

            CashierSession::query()->create([
                'user_id' => $row->cashier_id,
                'shift_start' => $shiftStart,
                'shift_end' => $shiftEnd,
                'total_sales' => (float) $row->total_sales,
                'total_transactions' => (int) $row->total_transactions,
            ]);
        }

        $cashierIds = User::query()->where('role', 'cashier')->pluck('id')->values();

        if ($cashierIds->isEmpty()) {
            return;
        }

        foreach ($cashierIds->random(min(3, $cashierIds->count())) as $cashierId) {
            CashierSession::query()->create([
                'user_id' => $cashierId,
                'shift_start' => now()->subHours(random_int(1, 4)),
                'shift_end' => null,
                'total_sales' => random_int(150000, 900000),
                'total_transactions' => random_int(15, 60),
            ]);
        }
    }

    private function seedFinancialData(): void
    {
        $dailySales = Order::query()
            ->where('is_paid', true)
            ->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_SELESAI])
            ->selectRaw('DATE(created_at) as sales_date, COALESCE(SUM(total_amount), 0) as total_sales')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sales_date')
            ->get();

        foreach ($dailySales as $row) {
            Income::query()->create([
                'source' => 'POS Sales',
                'category' => 'sales',
                'amount' => (float) $row->total_sales,
                'date' => $row->sales_date,
                'description' => 'Auto generated from paid orders',
            ]);
        }

        $faker = fake('id_ID');

        $additionalIncomeCategories = ['services', 'investment', 'other'];
        for ($i = 1; $i <= 18; $i++) {
            Income::query()->create([
                'source' => 'Other Income ' . $i,
                'category' => $additionalIncomeCategories[array_rand($additionalIncomeCategories)],
                'amount' => random_int(50000, 750000),
                'date' => now()->subDays(random_int(0, 90))->toDateString(),
                'description' => $faker->sentence(8),
            ]);
        }

        $expenseCategories = ['inventory', 'utilities', 'rent', 'maintenance', 'salary', 'marketing', 'other'];
        $expenseVendors = ['PT Bahan Makmur', 'CV Sumber Pangan', 'PLN', 'PDAM', 'Toko Grosir Harian', 'Supplier Frozen'];
        $paymentMethods = ['cash', 'bank_transfer', 'qris'];

        for ($i = 1; $i <= 140; $i++) {
            Expense::query()->create([
                'vendor' => $expenseVendors[array_rand($expenseVendors)],
                'category' => $expenseCategories[array_rand($expenseCategories)],
                'amount' => random_int(35000, 1800000),
                'date' => now()->subDays(random_int(0, 90))->toDateString(),
                'description' => $faker->sentence(10),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
            ]);
        }

        $payLaterOrders = Order::query()
            ->where('payment_method', 'bayar_nanti')
            ->where('is_paid', false)
            ->inRandomOrder()
            ->limit(80)
            ->get();

        foreach ($payLaterOrders as $order) {
            $invoiceDate = Carbon::parse($order->created_at);
            $paidAmount = random_int(0, 100) <= 40
                ? round((float) $order->total_amount * random_int(20, 70) / 100, 2)
                : 0;

            $status = 'pending';
            if ($paidAmount > 0 && $paidAmount < (float) $order->total_amount) {
                $status = Receivable::STATUS_PARTIAL;
            }

            $dueDate = $invoiceDate->copy()->addDays(random_int(5, 14));
            if ($status !== Receivable::STATUS_PARTIAL && $dueDate->isPast()) {
                $status = Receivable::STATUS_OVERDUE;
            }

            Receivable::query()->create([
                'customer_name' => $order->customer_name ?: 'Customer Bayar Nanti',
                'amount' => (float) $order->total_amount,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => $status,
                'paid_amount' => $paidAmount,
                'notes' => 'Generated from order ' . $order->order_code,
            ]);
        }
    }

    private function seedAdjustmentsAndWaste(): void
    {
        $ingredientIds = Ingredient::query()->pluck('id')->values();
        $recorderIds = User::query()->whereIn('role', ['admin', 'cashier'])->pluck('id')->values();

        if ($ingredientIds->isEmpty() || $recorderIds->isEmpty()) {
            return;
        }

        $increaseReasons = [
            'Koreksi opname: stok fisik lebih tinggi',
            'Pencatatan pembelian terlambat',
            'Tambahan stok dari supplier darurat',
        ];

        $decreaseReasons = [
            'Koreksi opname: stok fisik lebih rendah',
            'Bahan rusak saat penyimpanan',
            'Kesalahan takaran produksi',
        ];

        for ($i = 1; $i <= 24; $i++) {
            $type = random_int(1, 100) <= 55 ? 'increase' : 'decrease';
            $reason = $type === 'increase'
                ? $increaseReasons[array_rand($increaseReasons)]
                : $decreaseReasons[array_rand($decreaseReasons)];

            try {
                $this->stockReconciliationService->createManualAdjustment(
                    ingredientId: $ingredientIds->random(),
                    quantity: random_int(5, 90),
                    adjustmentType: $type,
                    reason: $reason,
                    recordedBy: $recorderIds->random(),
                    reference: 'ADJ-' . strtoupper(substr($type, 0, 3)) . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    approvedBy: null,
                    ingredientBatchId: null,
                );
            } catch (\Throwable) {
                // Skip adjustment that fails validation due edge cases and continue seeding.
            }
        }

        $wasteReasons = [
            'Tumpah saat operasional',
            'Bahan kadaluarsa',
            'Kesalahan produksi',
            'Kontaminasi bahan',
            'Produk rusak saat penyimpanan',
        ];

        for ($i = 1; $i <= 36; $i++) {
            WasteRecord::query()->create([
                'ingredient_id' => $ingredientIds->random(),
                'quantity' => random_int(2, 35),
                'reason' => $wasteReasons[array_rand($wasteReasons)],
                'recorded_by' => $recorderIds->random(),
            ]);
        }
    }
}
