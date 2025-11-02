<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class CustomerClusteringChart extends ChartWidget
{
    protected ?string $heading = 'Segmentasi Pelanggan (K-Means Clustering)';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        // Data dummy dari K-Means clustering
        // Real data akan dari Python API atau database
        
        return [
            'datasets' => [
                [
                    'label' => 'Loyal Customers',
                    'data' => 145, // Jumlah pelanggan
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                ],
                [
                    'label' => 'Occasional Customers',
                    'data' => 98,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                ],
                [
                    'label' => 'New Customers',
                    'data' => 67,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.8)',
                ],
                [
                    'label' => 'At-Risk Customers',
                    'data' => 23,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                ],
            ],
            'labels' => ['Loyal', 'Occasional', 'New', 'At-Risk'],
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
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
