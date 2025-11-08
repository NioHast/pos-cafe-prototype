<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DataMining extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Data Mining';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Data Mining & Analytics';
    protected string $view = 'filament.pages.data-mining';
    public function getMockAssociationData(): array
    {
        return [
            ['rule' => 'Coffee → Pastry', 'support' => '45%', 'confidence' => '72%', 'lift' => '2.3'],
            ['rule' => 'Latte → Croissant', 'support' => '38%', 'confidence' => '65%', 'lift' => '1.8'],
            ['rule' => 'Espresso → Biscuit', 'support' => '32%', 'confidence' => '58%', 'lift' => '1.5'],
            ['rule' => 'Tea → Cake', 'support' => '28%', 'confidence' => '54%', 'lift' => '1.4'],
        ];
    }

    public function getMockClusterData(): array
    {
        return [
            ['cluster' => 'High Spender', 'count' => '342', 'avg_spend' => 'Rp 150,000', 'frequency' => '4.2/week'],
            ['cluster' => 'Regular Customer', 'count' => '789', 'avg_spend' => 'Rp 75,000', 'frequency' => '2.1/week'],
            ['cluster' => 'Occasional Visitor', 'count' => '1,234', 'avg_spend' => 'Rp 35,000', 'frequency' => '0.8/week'],
        ];
    }

    public function getMockPredictionData(): array
    {
        return [
            'series' => [[
                'name' => 'Actual',
                'data' => [120, 135, 142, 138, 155, 148, 162, 175, 168, 180, 192, 185]
            ], [
                'name' => 'Predicted',
                'data' => [null, null, null, null, null, null, null, null, null, 178, 195, 202]
            ]],
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        ];
    }

    public function getMockStockData(): array
    {
        return [
            'series' => [65, 25, 10],
            'labels' => ['Optimal', 'Low Stock', 'Critical']
        ];
    }
}
