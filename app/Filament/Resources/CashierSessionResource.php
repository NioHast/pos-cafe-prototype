<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashierSessionResource\Pages;
use App\Models\CashierSession;
use Filament\Forms\Components;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashierSessionResource extends Resource
{
    protected static ?string $model = CashierSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Sesi Kasir';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Informasi Sesi')
                    ->schema([
                        Components\Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Components\DateTimePicker::make('shift_start')
                            ->label('Waktu Mulai')
                            ->required()
                            ->default(now()),
                        Components\DateTimePicker::make('shift_end')
                            ->label('Waktu Selesai')
                            ->nullable(),
                    ])->columns(3),

                SchemaComponents\Section::make('Statistik')
                    ->schema([
                        Components\TextInput::make('total_sales')
                            ->label('Total Penjualan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Components\TextInput::make('total_transactions')
                            ->label('Jumlah Transaksi')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift_start')
                    ->label('Mulai Shift')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift_end')
                    ->label('Selesai Shift')
                    ->dateTime('d M Y, H:i')
                    ->default('-')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->isActive() ? 'Aktif' : 'Selesai')
                    ->colors([
                        'success' => 'Aktif',
                        'secondary' => 'Selesai',
                    ]),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Transaksi')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi (jam)')
                    ->getStateUsing(fn ($record) => $record->duration ? number_format($record->duration, 1) : '-')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Sesi Aktif')
                    ->query(fn ($query) => $query->active()),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn ($query) => $query->today()),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('shift_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashierSessions::route('/'),
            'create' => Pages\CreateCashierSession::route('/create'),
            'view' => Pages\ViewCashierSession::route('/{record}'),
            'edit' => Pages\EditCashierSession::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
