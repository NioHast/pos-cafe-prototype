<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Menu;
use App\Support\DemoAdminMode;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        if (DemoAdminMode::enabled()) {
            return [
                Stat::make('Penjualan Hari Ini', 'Rp 1.250.000')
                    ->description('↑ 12.5% dari kemarin')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart([850000, 910000, 780000, 990000, 1150000, 1220000, 1250000]),

                Stat::make('Menu Tersedia', '9')
                    ->description('3 kategori aktif')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('info'),

                Stat::make('Pesanan Bulan Ini', '247')
                    ->description('↑ 8.2% dari bulan lalu')
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('primary'),
            ];
        }

        $salesToday     = Order::whereDate('created_at', today())->where('is_paid', true)->sum('total_amount');
        $salesYesterday = Order::whereDate('created_at', today()->subDay())->where('is_paid', true)->sum('total_amount');
        $salesChange    = $salesYesterday > 0
            ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1)
            : 0;

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $last7Days[] = Order::whereDate('created_at', today()->subDays($i))
                ->where('is_paid', true)
                ->sum('total_amount');
        }

        $totalMenu       = Menu::where('is_available', true)->count();
        $totalCategories = Menu::distinct('category_id')->count();

        $ordersThisMonth = Order::whereMonth('created_at', today()->month)
            ->whereYear('created_at', today()->year)
            ->count();
        $ordersLastMonth = Order::whereMonth('created_at', today()->subMonth()->month)
            ->whereYear('created_at', today()->subMonth()->year)
            ->count();
        $ordersChange = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : 0;

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($salesToday, 0, ',', '.'))
                ->description(($salesChange >= 0 ? '↑ ' : '↓ ') . abs($salesChange) . '% dari kemarin')
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart($last7Days),

            Stat::make('Menu Tersedia', $totalMenu)
                ->description($totalCategories . ' kategori aktif')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Pesanan Bulan Ini', $ordersThisMonth)
                ->description(($ordersChange >= 0 ? '↑ ' : '↓ ') . abs($ordersChange) . '% dari bulan lalu')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}
