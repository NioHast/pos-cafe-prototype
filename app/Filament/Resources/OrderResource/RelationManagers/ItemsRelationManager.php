<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Components;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items Pesanan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('menu_id')
                    ->label('Menu')
                    ->relationship('menu', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $menu = \App\Models\Menu::find($state);
                            if ($menu) {
                                $set('price_at_transaction', $menu->price);
                            }
                        }
                    }),
                Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Components\TextInput::make('price_at_transaction')
                    ->label('Harga Satuan')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->helperText('Harga akan otomatis terisi dari menu'),
                Components\Select::make('handled_by')
                    ->label('Dibuat Oleh')
                    ->relationship('handler', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price_at_transaction')
                    ->label('Harga')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price_at_transaction),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
