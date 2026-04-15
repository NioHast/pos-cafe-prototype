<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WasteRecordResource\Pages\ListWasteRecords;
use App\Filament\Resources\WasteRecordResource\Pages\CreateWasteRecord;
use App\Filament\Resources\WasteRecordResource\Pages\EditWasteRecord;
use App\Filament\Resources\WasteRecordResource\Pages;
use App\Models\WasteRecord;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WasteRecordResource extends Resource
{
    protected static ?string $model = WasteRecord::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-trash';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Waste Records';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ingredient_id')
                ->label('Ingredient')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->unit . ')'),
            TextInput::make('quantity')
                ->label('Wasted Quantity')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->step(0.01),
            Textarea::make('reason')
                ->label('Reason')
                ->required()
                ->rows(3)
                ->maxLength(65535),
            Hidden::make('recorded_by')
                ->default(fn () => Auth::id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'recordedBy']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Ingredient')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->formatStateUsing(function ($state, WasteRecord $record) {
                        $unit = $record->ingredient?->unit ? (' ' . $record->ingredient->unit) : '';

                        return number_format((float) $state, 2) . $unit;
                    }),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(fn (WasteRecord $record) => $record->reason),
                TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->default('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ingredient')
                    ->relationship('ingredient', 'name')
                    ->label('Ingredient'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWasteRecords::route('/'),
            'create' => CreateWasteRecord::route('/create'),
            'edit' => EditWasteRecord::route('/{record}/edit'),
        ];
    }
}
