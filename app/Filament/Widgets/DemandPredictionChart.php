<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DemandPredictionChart extends ChartWidget
{
    protected ?string $heading = 'Prediksi Permintaan Menu (Random Forest)';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        // Data historical + prediksi
        return [
            'datasets' => [
                [
                    'label' => 'Penjualan Aktual',
                    'data' => [120, 145, 98, 134, 156, 178, 192, null, null, null, null, null, null, null],
                    'borderColor' => 'rgb(79, 70, 229)',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Prediksi',
                    'data' => [null, null, null, null, null, null, 192, 205, 215, 230, 245, 260, 275, 290],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderDash' => [5, 5],
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                'Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7',
                'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12', 'Week 13', 'Week 14'
            ],
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
                'annotation' => [
                    'annotations' => [
                        'line1' => [
                            'type' => 'line',
                            'xMin' => 6,
                            'xMax' => 6,
                            'borderColor' => 'rgb(255, 99, 132)',
                            'borderWidth' => 2,
                            'borderDash' => [10, 5],
                            'label' => [
                                'content' => 'Today',
                                'enabled' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
