<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;

class AssociationRulesChart extends ChartWidget
{
    protected ?string $heading = 'Kombinasi Menu Terpopuler (Association Mining)';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        // Ambil data dari Python API atau database
        // Contoh: $results = Http::get('http://python-api:8001/analytics/association')->json();
        
        // Data dummy untuk demo
        $rules = [
            ['combo' => 'Kopi Susu + Donat', 'confidence' => 0.78],
            ['combo' => 'Matcha Latte + Cake', 'confidence' => 0.72],
            ['combo' => 'Cappuccino + Croissant', 'confidence' => 0.68],
            ['combo' => 'Teh Tarik + Roti Bakar', 'confidence' => 0.65],
            ['combo' => 'Espresso + Brownies', 'confidence' => 0.58],
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Confidence Score',
                    'data' => array_column($rules, 'confidence'),
                    'backgroundColor' => [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                    ],
                ],
            ],
            'labels' => array_column($rules, 'combo'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Horizontal bar
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Confidence: " + (context.parsed.x * 100).toFixed(1) + "%"; }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'max' => 1,
                    'ticks' => [
                        'callback' => 'function(value) { return (value * 100) + "%"; }',
                    ],
                ],
            ],
        ];
    }
}
