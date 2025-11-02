<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Penjualan 7 Hari Terakhir';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get last 7 days sales data
        $salesData = [];
        $labels = [];
        $targetData = [];
        $dailyTarget = 400000; // Target harian Rp 400,000

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sales = Order::paid()->whereDate('created_at', $date)->sum('total_price');
            
            $salesData[] = $sales;
            $labels[] = $date->translatedFormat('D'); // Sen, Sel, Rab, dll
            $targetData[] = $dailyTarget;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (Rp)',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)', // Indigo transparent
                    'borderColor' => 'rgb(79, 70, 229)', // Indigo solid
                    'fill' => true,
                    'tension' => 0.4, // Smooth curve
                ],
                [
                    'label' => 'Target (Rp)',
                    'data' => $targetData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)', // Red transparent
                    'borderColor' => 'rgb(239, 68, 68)', // Red solid
                    'borderDash' => [5, 5], // Dashed line
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}

