<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuIngredients';

    protected static ?string $title = 'Resep (Bahan)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('ingredient_id')
                    ->label('Bahan')
                    ->relationship('ingredient', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->unit})"),
                Components\TextInput::make('quantity_used')
                    ->label('Jumlah yang Digunakan')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->helperText('Jumlah bahan per porsi'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ingredient.name')
            ->columns([
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_used')
                    ->label('Jumlah/Porsi')
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . $record->ingredient->unit),
                Tables\Columns\TextColumn::make('ingredient.total_stock')
                    ->label('Stok Tersedia')
                    ->getStateUsing(fn ($record) => number_format($record->ingredient->getTotalStock(), 2))
                    ->suffix(fn ($record) => ' ' . $record->ingredient->unit)
                    ->badge()
                    ->color(fn ($record) => $record->ingredient->getTotalStock() < $record->ingredient->low_stock_threshold ? 'danger' : 'success'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
