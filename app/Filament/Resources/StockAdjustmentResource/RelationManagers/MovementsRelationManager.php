<?php

namespace App\Filament\Resources\StockAdjustmentResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Stock Movements';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredientBatch', 'recordedBy']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label('Movement')
                    ->badge()
                    ->sortable(),
                TextColumn::make('ingredient_batch_id')
                    ->label('Batch')
                    ->default('-')
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Before')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('quantity_change')
                    ->label('Change')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('After')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->default('-'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('id', 'desc');
    }
}