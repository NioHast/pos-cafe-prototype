<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class StockLevelChart extends ChartWidget
{
    protected ?string $heading = 'Status Stok Bahan';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Bahan',
                    'data' => [12, 5, 3], // Normal, Low Stock, Out of Stock
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',  // Green - Normal
                        'rgba(245, 158, 11, 0.8)',  // Orange - Low
                        'rgba(239, 68, 68, 0.8)',   // Red - Out
                    ],
                    'borderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Stok Normal', 'Stok Rendah', 'Habis'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}

