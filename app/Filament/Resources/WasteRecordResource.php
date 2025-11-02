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

    protected static ?string $modelLabel = 'Pencatatan Waste';

    protected static ?string $pluralModelLabel = 'Pencatatan Waste';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('ingredient_id')
                    ->label('Bahan')
                    ->relationship('ingredient', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->unit})"),
                Components\TextInput::make('quantity')
                    ->label('Jumlah Terbuang')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Components\Textarea::make('reason')
                    ->label('Alasan')
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
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . $record->ingredient->unit),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->reason),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Dicatat Oleh')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ingredient')
                    ->relationship('ingredient', 'name')
                    ->label('Bahan'),
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
