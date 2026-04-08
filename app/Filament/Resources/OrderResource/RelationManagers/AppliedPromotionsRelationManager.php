<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AppliedPromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'appliedPromotions';

    protected static ?string $title = 'Applied Promotions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->placeholder('Deleted Promotion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Value')
                    ->formatStateUsing(fn ($record) => $record->discount_type === 'percentage'
                        ? $record->discount_value . '%'
                        : 'Rp ' . number_format($record->discount_value, 0, ',', '.')),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Amount Saved')
                    ->money('IDR')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied At')
                    ->dateTime('d M Y, H:i'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
