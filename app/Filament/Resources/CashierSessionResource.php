<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CashierSessionResource\Pages\ListCashierSessions;
use App\Filament\Resources\CashierSessionResource\Pages\CreateCashierSession;
use App\Filament\Resources\CashierSessionResource\Pages\EditCashierSession;
use App\Filament\Resources\CashierSessionResource\Pages;
use App\Models\CashierSession;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashierSessionResource extends Resource
{
    protected static ?string $model = CashierSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Cashier Sessions';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Cashier')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            DateTimePicker::make('shift_start')
                ->label('Start Time')
                ->required()
                ->default(now())
                ->native(false),
            DateTimePicker::make('shift_end')
                ->label('End Time')
                ->nullable()
                ->native(false),
            TextInput::make('total_sales')
                ->label('Total Sales')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('Rp'),
            TextInput::make('total_transactions')
                ->label('Total Transactions')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Session ID')
                    ->limit(12)
                    ->tooltip(fn (CashierSession $record) => $record->id)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shift_start')
                    ->label('Shift Start')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('shift_end')
                    ->label('Shift End')
                    ->dateTime('d M Y, H:i')
                    ->default('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (CashierSession $record) => $record->isActive() ? 'Active' : 'Completed')
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray'),
                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_transactions')
                    ->label('Transactions')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duration (hours)')
                    ->getStateUsing(fn (CashierSession $record) => $record->duration !== null ? number_format($record->duration, 2) : '-')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active Sessions')
                    ->query(fn ($query) => $query->active()),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn ($query) => $query->today()),
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
            ->defaultSort('shift_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashierSessions::route('/'),
            'create' => CreateCashierSession::route('/create'),
            'edit' => EditCashierSession::route('/{record}/edit'),
        ];
    }
}