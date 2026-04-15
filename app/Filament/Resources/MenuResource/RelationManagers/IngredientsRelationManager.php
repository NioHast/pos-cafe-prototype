<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuIngredients';

    protected static ?string $title = 'Resep (Bahan)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ingredient_id')
                ->label('Bahan')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->unit . ')'),
            TextInput::make('quantity_used')
                ->label('Jumlah per Porsi')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->step(0.01),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_used')
                    ->label('Jumlah/Porsi')
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . ($record->ingredient->unit ?? '')),
                TextColumn::make('ingredient.total_stock')
                    ->label('Stok Tersedia')
                    ->getStateUsing(fn ($record) => number_format($record->ingredient?->getTotalStock() ?? 0, 2))
                    ->suffix(fn ($record) => ' ' . ($record->ingredient->unit ?? ''))
                    ->badge()
                    ->color(fn ($record) => ($record->ingredient?->getTotalStock() ?? 0) < ($record->ingredient->low_stock_threshold ?? 0) ? 'danger' : 'success'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}