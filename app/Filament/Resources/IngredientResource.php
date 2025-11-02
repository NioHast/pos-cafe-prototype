<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Filament\Resources\IngredientResource\RelationManagers;
use App\Models\Ingredient;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\TextInput::make('name')
                    ->label('Nama Bahan')
                    ->required()
                    ->maxLength(255),
                Components\TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('gram, ml, pcs'),
                Components\TextInput::make('low_stock_threshold')
                    ->label('Ambang Batas Stok Rendah')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label('Ambang Stok Rendah')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->getStateUsing(fn (Ingredient $record) => number_format($record->getTotalStock(), 2))
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->getTotalStock() < $record->low_stock_threshold ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn ($query) => $query->whereHas('batches', function ($q) {
                        $q->havingRaw('SUM(quantity) < ingredients.low_stock_threshold');
                    })),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
