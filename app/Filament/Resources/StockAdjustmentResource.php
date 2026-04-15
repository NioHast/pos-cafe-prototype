<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\StockAdjustmentResource\Pages\ListStockAdjustments;
use App\Filament\Resources\StockAdjustmentResource\Pages\CreateStockAdjustment;
use App\Filament\Resources\StockAdjustmentResource\Pages\EditStockAdjustment;
use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock Adjustments';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ingredient_id')
                ->label('Ingredient')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->disabledOn('edit')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->unit . ')'),
            Select::make('adjustment_type')
                ->label('Adjustment Type')
                ->options([
                    StockAdjustment::TYPE_INCREASE => 'Increase',
                    StockAdjustment::TYPE_DECREASE => 'Decrease',
                ])
                ->required()
                ->native(false)
                ->live()
                ->disabledOn('edit'),
            TextInput::make('quantity')
                ->label('Quantity')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->step(0.01)
                ->disabledOn('edit'),
            Select::make('ingredient_batch_id')
                ->label('Target Batch (optional)')
                ->options(function (Get $get) {
                    $ingredientId = (int) ($get('ingredient_id') ?? 0);

                    if ($ingredientId <= 0) {
                        return [];
                    }

                    return IngredientBatch::query()
                        ->where('ingredient_id', $ingredientId)
                        ->orderByDesc('received_at')
                        ->get()
                        ->mapWithKeys(function (IngredientBatch $batch) {
                            $receivedAt = $batch->received_at?->format('d M Y H:i') ?? '-';
                            $label = 'Batch #' . $batch->id . ' | Qty ' . number_format((float) $batch->quantity, 2) . ' | Received ' . $receivedAt;

                            return [$batch->id => $label];
                        })
                        ->all();
                })
                ->searchable()
                ->visible(fn (Get $get): bool => $get('adjustment_type') === StockAdjustment::TYPE_INCREASE)
                ->helperText('Kosongkan untuk menggunakan batch terbaru berdasarkan waktu received_at.')
                ->disabledOn('edit'),
            Textarea::make('reason')
                ->label('Reason')
                ->required()
                ->rows(3)
                ->maxLength(65535)
                ->disabledOn('edit'),
            TextInput::make('reference')
                ->label('Reference')
                ->maxLength(255)
                ->nullable()
                ->disabledOn('edit'),
            Select::make('approved_by')
                ->label('Approved By')
                ->relationship('approvedBy', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->disabledOn('edit'),
            DateTimePicker::make('adjusted_at')
                ->label('Adjusted At')
                ->seconds(false)
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'recordedBy', 'approvedBy']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Ingredient')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'Increase' : 'Decrease'),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (StockAdjustment $record) => ' ' . ($record->ingredient?->unit ?? ''))
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Before')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('After')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->default('-')
                    ->sortable(),
                TextColumn::make('adjusted_at')
                    ->label('Adjusted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ingredient')
                    ->relationship('ingredient', 'name')
                    ->label('Ingredient'),
                SelectFilter::make('adjustment_type')
                    ->label('Type')
                    ->options([
                        StockAdjustment::TYPE_INCREASE => 'Increase',
                        StockAdjustment::TYPE_DECREASE => 'Decrease',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Detail'),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAdjustments::route('/'),
            'create' => CreateStockAdjustment::route('/create'),
            'edit' => EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}