<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\IngredientResource\RelationManagers\BatchesRelationManager;
use App\Filament\Resources\IngredientResource\Pages\ListIngredients;
use App\Filament\Resources\IngredientResource\Pages\CreateIngredient;
use App\Filament\Resources\IngredientResource\Pages\EditIngredient;
use App\Models\Ingredient;
use App\Support\DemoAdminData;
use App\Support\DemoAdminMode;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Ingredients';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Ingredient Name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('unit')
                ->label('Unit')
                ->options(Ingredient::UNITS)
                ->required()
                ->searchable()
                ->native(false),
            TextInput::make('low_stock_threshold')
                ->label('Low Stock Threshold')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->default(0),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        if (DemoAdminMode::enabled()) {
            return $table
                ->columns([
                    TextColumn::make('name')
                        ->label('Ingredient Name')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('unit')
                        ->label('Unit')
                        ->sortable(),
                    TextColumn::make('low_stock_threshold')
                        ->label('Low Stock Threshold')
                        ->formatStateUsing(fn (mixed $state, array $record): string => number_format((float) $state, 0, ',', '.') . ' ' . ($record['unit'] ?? ''))
                        ->sortable(),
                    TextColumn::make('total_stock')
                        ->label('Total Stock')
                        ->formatStateUsing(fn (mixed $state, array $record): string => number_format((float) $state, 0, ',', '.') . ' ' . ($record['unit'] ?? ''))
                        ->badge()
                        ->color(fn (array $record): string => (float) ($record['total_stock'] ?? 0) < (float) ($record['low_stock_threshold'] ?? 0) ? 'danger' : 'success'),
                    IconColumn::make('is_active')
                        ->label('Active')
                        ->boolean()
                        ->sortable(),
                ])
                ->records(fn (?string $search = null, ?string $sortColumn = null, ?string $sortDirection = null, int | string $page = 1, int | string $recordsPerPage = 10) => DemoAdminData::forTable('ingredients', $search, $sortColumn, $sortDirection, $page, $recordsPerPage))
                ->filters([])
                ->recordActions([])
                ->toolbarActions([])
                ->defaultSort('name', 'asc');
        }

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ingredient Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label('Low Stock Threshold')
                    ->formatStateUsing(fn (mixed $state, Ingredient $record): string => number_format((float) $state, 0, ',', '.') . ' ' . $record->unit)
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->getStateUsing(fn (Ingredient $record): float => $record->getTotalStock())
                    ->formatStateUsing(fn (mixed $state, Ingredient $record): string => number_format((float) $state, 0, ',', '.') . ' ' . $record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->getTotalStock() < (float) $record->low_stock_threshold ? 'danger' : 'success'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn ($query) => $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) < low_stock_threshold')),
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        if (DemoAdminMode::enabled()) {
            return [];
        }

        return [
            BatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        if (DemoAdminMode::enabled()) {
            return [
                'index' => ListIngredients::route('/'),
            ];
        }

        return [
            'index' => ListIngredients::route('/'),
            'create' => CreateIngredient::route('/create'),
            'edit' => EditIngredient::route('/{record}/edit'),
        ];
    }
}
