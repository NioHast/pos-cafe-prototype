<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularMenuChart extends ChartWidget
{
    protected ?string $heading = 'Menu Terlaris Minggu Ini';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get top 5 menu by quantity sold in last 7 days
        $topMenu = OrderItem::select('menu_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($query) {
                $query->paid()->where('created_at', '>=', now()->subDays(7));
            })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];
        
        foreach ($topMenu as $item) {
            $labels[] = $item->menu->name;
            $data[] = $item->total_sold;
        }

        // Add dummy data if not enough
        if (count($labels) < 5) {
            $dummyLabels = ['Kopi Susu', 'Matcha Latte', 'Coklat Panas', 'Es Teh', 'Cappuccino'];
            while (count($labels) < 5) {
                $labels[] = $dummyLabels[count($labels)] ?? 'Menu ' . (count($labels) + 1);
                $data[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Terjual (porsi)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(79, 70, 229, 0.8)',   // Indigo
                        'rgba(16, 185, 129, 0.8)',  // Green
                        'rgba(245, 158, 11, 0.8)',  // Orange
                        'rgba(239, 68, 68, 0.8)',   // Red
                        'rgba(59, 130, 246, 0.8)',  // Blue
                    ],
                    'borderColor' => [
                        'rgb(79, 70, 229)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 20,
                    ],
                ],
            ],
        ];
    }
}

