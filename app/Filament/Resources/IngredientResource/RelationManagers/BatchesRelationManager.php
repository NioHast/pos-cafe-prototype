<?php

namespace App\Filament\Resources\IngredientResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Batch Stock';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('quantity')
                ->label('Quantity')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01),
            DatePicker::make('expiry_date')
                ->label('Expiry Date')
                ->native(false),
            DateTimePicker::make('received_at')
                ->label('Received At')
                ->required()
                ->default(now())
                ->native(false),
            TextInput::make('cost_per_unit')
                ->label('Cost per Unit')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),
        ]);
    }

    public function table(Table $table): Table
    {
        $unit = $this->getOwnerRecord()->unit;

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ' . $unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->color(function ($record) {
                        if (! $record->expiry_date) {
                            return 'gray';
                        }

                        if ($record->expiry_date->isPast()) {
                            return 'danger';
                        }

                        if ($record->expiry_date->diffInDays(now()) < 7) {
                            return 'warning';
                        }

                        return 'success';
                    }),
                TextColumn::make('received_at')
                    ->label('Received At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cost_per_unit')
                    ->label('Cost/Unit')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
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
            ->defaultSort('expiry_date', 'asc');
    }
}
