<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteRecordResource\Pages;
use App\Models\WasteRecord;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
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

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Waste Record';

    protected static ?string $pluralModelLabel = 'Waste Records';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('ingredient_id')
                    ->label('Ingredient')
                    ->relationship('ingredient', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->unit})"),
                Components\TextInput::make('quantity')
                    ->label('Wasted Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Components\Textarea::make('reason')
                    ->label('Reason')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535),
                Components\Hidden::make('recorded_by')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('Ingredient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . $record->ingredient->unit),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->reason),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ingredient')
                    ->relationship('ingredient', 'name')
                    ->label('Ingredient'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteRecords::route('/'),
            'create' => Pages\CreateWasteRecord::route('/create'),
            'edit' => Pages\EditWasteRecord::route('/{record}/edit'),
        ];
    }
}
