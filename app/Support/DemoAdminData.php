<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DemoAdminData
{
    public static function forTable(
        string $resource,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
        int | string $page = 1,
        int | string $recordsPerPage = 10,
    ): Collection | LengthAwarePaginator {
        $records = collect(static::records($resource))
            ->values()
            ->map(function (array $record, int $index): array {
                $record['__key'] = (string) ($record['__key'] ?? ($index + 1));

                return $record;
            });

        if (filled($search)) {
            $needle = mb_strtolower((string) $search);

            $records = $records
                ->filter(function (array $record) use ($needle): bool {
                    $haystack = mb_strtolower(json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

                    return str_contains($haystack, $needle);
                })
                ->values();
        }

        if (filled($sortColumn)) {
            $descending = $sortDirection === 'desc';

            $records = $records
                ->sortBy(
                    fn (array $record) => static::normalizeSortValue(data_get($record, $sortColumn)),
                    options: SORT_NATURAL | SORT_FLAG_CASE,
                    descending: $descending,
                )
                ->values();
        }

        if ($recordsPerPage === 'all') {
            return $records;
        }

        $perPage = max((int) $recordsPerPage, 1);
        $currentPage = max((int) $page, 1);
        $items = $records->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $records->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function records(string $resource): array
    {
        return match ($resource) {
            'categories' => [
                ['name' => 'Coffee', 'slug' => 'coffee', 'menus_count' => 4, 'is_active' => true],
                ['name' => 'Non Coffee', 'slug' => 'non-coffee', 'menus_count' => 3, 'is_active' => true],
                ['name' => 'Snack', 'slug' => 'snack', 'menus_count' => 2, 'is_active' => true],
            ],
            'menus' => [
                [
                    'name' => 'Americano',
                    'category' => ['name' => 'Coffee'],
                    'price' => 18000,
                    'cashback' => 2000,
                    'is_available' => true,
                    'is_student_discount' => true,
                    'is_stock_calculated' => true,
                ],
                [
                    'name' => 'Cappuccino',
                    'category' => ['name' => 'Coffee'],
                    'price' => 24000,
                    'cashback' => 2000,
                    'is_available' => true,
                    'is_student_discount' => true,
                    'is_stock_calculated' => true,
                ],
                [
                    'name' => 'Matcha Latte',
                    'category' => ['name' => 'Non Coffee'],
                    'price' => 22000,
                    'cashback' => 1000,
                    'is_available' => true,
                    'is_student_discount' => true,
                    'is_stock_calculated' => false,
                ],
                [
                    'name' => 'French Fries',
                    'category' => ['name' => 'Snack'],
                    'price' => 15000,
                    'cashback' => 0,
                    'is_available' => false,
                    'is_student_discount' => false,
                    'is_stock_calculated' => false,
                ],
            ],
            'orders' => [
                [
                    'order_code' => 'ORD-20260423-001',
                    'customer_name' => 'Budi Santoso',
                    'cashier' => ['name' => 'Kasir A'],
                    'total_amount' => 52000,
                    'payment_method' => 'qris',
                    'status' => 'pending',
                    'is_paid' => false,
                    'created_at' => '2026-04-23 08:15:00',
                ],
                [
                    'order_code' => 'ORD-20260423-002',
                    'customer_name' => 'Siti Rahma',
                    'cashier' => ['name' => 'Kasir B'],
                    'total_amount' => 76000,
                    'payment_method' => 'cash',
                    'status' => 'diproses',
                    'is_paid' => true,
                    'created_at' => '2026-04-23 09:04:00',
                ],
                [
                    'order_code' => 'ORD-20260423-003',
                    'customer_name' => 'Andi Pratama',
                    'cashier' => ['name' => 'Kasir A'],
                    'total_amount' => 33000,
                    'payment_method' => 'bayar_nanti',
                    'status' => 'selesai',
                    'is_paid' => false,
                    'created_at' => '2026-04-23 10:20:00',
                ],
            ],
            'cashier_sessions' => [
                [
                    'id' => 'CS-001',
                    'user' => ['name' => 'Kasir A'],
                    'shift_start' => '2026-04-23 07:00:00',
                    'shift_end' => null,
                    'status' => 'Active',
                    'total_sales' => 350000,
                    'total_transactions' => 18,
                    'duration' => '4.5',
                ],
                [
                    'id' => 'CS-000',
                    'user' => ['name' => 'Kasir B'],
                    'shift_start' => '2026-04-22 13:00:00',
                    'shift_end' => '2026-04-22 21:00:00',
                    'status' => 'Completed',
                    'total_sales' => 480000,
                    'total_transactions' => 24,
                    'duration' => '8.0',
                ],
            ],
            'incomes' => [
                ['date' => '2026-04-23', 'source' => 'Penjualan Harian', 'category' => 'sales', 'amount' => 1250000, 'description' => 'Akumulasi penjualan shift pagi'],
                ['date' => '2026-04-22', 'source' => 'Catering Kampus', 'category' => 'services', 'amount' => 850000, 'description' => 'Pesanan acara himpunan'],
                ['date' => '2026-04-20', 'source' => 'Top Up Modal', 'category' => 'investment', 'amount' => 2000000, 'description' => 'Tambahan modal operasional'],
            ],
            'expenses' => [
                ['date' => '2026-04-23', 'vendor' => 'Supplier Kopi Nusantara', 'category' => 'inventory', 'amount' => 650000, 'payment_method' => 'bank_transfer', 'description' => 'Pembelian biji kopi dan susu'],
                ['date' => '2026-04-22', 'vendor' => 'PLN', 'category' => 'utilities', 'amount' => 420000, 'payment_method' => 'cash', 'description' => 'Tagihan listrik bulanan'],
                ['date' => '2026-04-21', 'vendor' => 'Digital Ads', 'category' => 'marketing', 'amount' => 300000, 'payment_method' => 'debit_card', 'description' => 'Iklan media sosial'],
            ],
            'receivables' => [
                [
                    'customer_name' => 'UKM Kewirausahaan',
                    'invoice_date' => '2026-04-10',
                    'due_date' => '2026-04-25',
                    'amount' => 900000,
                    'paid_amount' => 300000,
                    'remaining_amount' => 600000,
                    'status' => 'partial',
                ],
                [
                    'customer_name' => 'BEM Fakultas',
                    'invoice_date' => '2026-04-02',
                    'due_date' => '2026-04-12',
                    'amount' => 500000,
                    'paid_amount' => 0,
                    'remaining_amount' => 500000,
                    'status' => 'overdue',
                ],
                [
                    'customer_name' => 'Komunitas Startup',
                    'invoice_date' => '2026-04-15',
                    'due_date' => '2026-04-30',
                    'amount' => 750000,
                    'paid_amount' => 750000,
                    'remaining_amount' => 0,
                    'status' => 'paid',
                ],
            ],
            'promotions' => [
                [
                    'name' => 'Diskon Mahasiswa 10%',
                    'type' => 'percentage',
                    'discount_value' => 10,
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-04-30',
                    'status' => 'active',
                    'usage_count' => 129,
                    'usage_limit' => 500,
                ],
                [
                    'name' => 'Promo Hemat Pagi',
                    'type' => 'fixed_amount',
                    'discount_value' => 5000,
                    'start_date' => '2026-04-15',
                    'end_date' => '2026-05-15',
                    'status' => 'scheduled',
                    'usage_count' => 0,
                    'usage_limit' => 300,
                ],
                [
                    'name' => 'Bundle Kopi + Snack',
                    'type' => 'bundle',
                    'discount_value' => 12000,
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-03-31',
                    'status' => 'expired',
                    'usage_count' => 87,
                    'usage_limit' => null,
                ],
            ],
            'ingredients' => [
                ['name' => 'Biji Kopi Arabika', 'unit' => 'gram', 'low_stock_threshold' => 500, 'total_stock' => 1350, 'is_active' => true],
                ['name' => 'Susu Full Cream', 'unit' => 'ml', 'low_stock_threshold' => 3000, 'total_stock' => 2500, 'is_active' => true],
                ['name' => 'Gula Aren', 'unit' => 'gram', 'low_stock_threshold' => 200, 'total_stock' => 180, 'is_active' => true],
                ['name' => 'Matcha Powder', 'unit' => 'gram', 'low_stock_threshold' => 150, 'total_stock' => 600, 'is_active' => true],
            ],
            'stock_adjustments' => [
                [
                    'id' => 301,
                    'ingredient' => ['name' => 'Susu Full Cream'],
                    'adjustment_type' => 'increase',
                    'quantity' => 1000,
                    'quantity_before' => 1500,
                    'quantity_after' => 2500,
                    'recordedBy' => ['name' => 'Admin Demo'],
                    'adjusted_at' => '2026-04-23 09:00:00',
                ],
                [
                    'id' => 302,
                    'ingredient' => ['name' => 'Gula Aren'],
                    'adjustment_type' => 'decrease',
                    'quantity' => 40,
                    'quantity_before' => 220,
                    'quantity_after' => 180,
                    'recordedBy' => ['name' => 'Admin Demo'],
                    'adjusted_at' => '2026-04-23 10:10:00',
                ],
            ],
            'waste_records' => [
                [
                    'id' => 71,
                    'ingredient' => ['name' => 'Susu Full Cream', 'unit' => 'ml'],
                    'quantity' => 250,
                    'reason' => 'Tumpah saat persiapan',
                    'recordedBy' => ['name' => 'Kasir A'],
                    'created_at' => '2026-04-22 14:12:00',
                ],
                [
                    'id' => 72,
                    'ingredient' => ['name' => 'French Fries', 'unit' => 'gram'],
                    'quantity' => 120,
                    'reason' => 'Melewati batas simpan',
                    'recordedBy' => ['name' => 'Kasir B'],
                    'created_at' => '2026-04-23 11:02:00',
                ],
            ],
            'daily_ingredient_usages' => [
                ['usage_date' => '2026-04-23', 'ingredient_name' => 'Biji Kopi Arabika', 'unit' => 'gram', 'jumlah_digunakan' => 780, 'created_at' => '2026-04-23 22:00:00'],
                ['usage_date' => '2026-04-23', 'ingredient_name' => 'Susu Full Cream', 'unit' => 'ml', 'jumlah_digunakan' => 1650, 'created_at' => '2026-04-23 22:00:00'],
                ['usage_date' => '2026-04-22', 'ingredient_name' => 'Gula Aren', 'unit' => 'gram', 'jumlah_digunakan' => 210, 'created_at' => '2026-04-22 22:00:00'],
            ],
            'users' => [
                ['name' => 'Admin Demo', 'email' => 'admin@w9cafe.com', 'role' => 'admin', 'created_at' => '2026-04-01 09:00:00'],
                ['name' => 'Kasir A', 'email' => 'kasir.a@w9cafe.com', 'role' => 'cashier', 'created_at' => '2026-04-03 08:30:00'],
                ['name' => 'Kasir B', 'email' => 'kasir.b@w9cafe.com', 'role' => 'cashier', 'created_at' => '2026-04-05 08:30:00'],
            ],
            default => [],
        };
    }

    public static function navigationBadge(string $resource): ?string
    {
        $count = count(static::records($resource));

        return $count > 0 ? (string) $count : null;
    }

    private static function normalizeSortValue(mixed $value): int | float | string
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return mb_strtolower((string) $value);
    }
}
