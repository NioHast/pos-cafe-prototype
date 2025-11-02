<?php

namespace App\Filament\Resources\IngredientResource\RelationManagers;

use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Batch Stok';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Components\DatePicker::make('expiry_date')
                    ->label('Tanggal Kadaluarsa')
                    ->required()
                    ->native(false),
                Components\DateTimePicker::make('received_at')
                    ->label('Tanggal Diterima')
                    ->required()
                    ->default(now())
                    ->native(false),
                Components\TextInput::make('cost_per_unit')
                    ->label('Harga per Unit')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('Rp'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Batch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . $record->ingredient->unit),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInDays(now()) < 7 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('Diterima')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Harga/Unit')
                    ->money('IDR')
                    ->sortable(),
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
            ])
            ->defaultSort('expiry_date', 'asc');
    }
}
