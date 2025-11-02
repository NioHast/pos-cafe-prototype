<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Menu;
use App\Models\Ingredient;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Sales today
        $salesToday = Order::paid()->today()->sum('total_price');
        $salesYesterday = Order::paid()->whereDate('created_at', today()->subDay())->sum('total_price');
        $salesChange = $salesYesterday > 0 
            ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1) 
            : 0;

        // Last 7 days sales for chart
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $last7Days[] = Order::paid()->whereDate('created_at', $date)->sum('total_price');
        }

        // Total menu
        $totalMenu = Menu::count();
        $totalCategories = Menu::distinct('category_id')->count();

        // Low stock ingredients
        $lowStock = Ingredient::whereRaw('
            (SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_id = ingredients.id) 
            < low_stock_threshold
        ')->count();

        // Orders this month
        $ordersThisMonth = Order::thisMonth()->count();
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
            
            Stat::make('Total Menu', $totalMenu)
                ->description($totalCategories . ' kategori tersedia')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            
            Stat::make('Stok Rendah', $lowStock . ' Bahan')
                ->description('Perlu restock segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),
            
            Stat::make('Pesanan Bulan Ini', $ordersThisMonth)
                ->description(($ordersChange >= 0 ? '↑ ' : '↓ ') . abs($ordersChange) . '% dari bulan lalu')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}

