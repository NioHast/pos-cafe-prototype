<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Informasi Pesanan')
                ->schema([
                    TextEntry::make('order_code')->label('Kode Pesanan')->copyable(),
                    TextEntry::make('created_at')->label('Tanggal')->dateTime('d M Y, H:i'),
                    TextEntry::make('cashier.name')->label('Kasir')->default('-'),
                ])->columns(3),

            Section::make('Pelanggan')
                ->schema([
                    TextEntry::make('customer_name')->label('Nama')->default('Guest'),
                    TextEntry::make('customer_phone')->label('No. HP')->default('-'),
                    TextEntry::make('order_type')->label('Jenis')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'qr' => 'info',
                            'cashier' => 'gray',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'qr' => 'QR Pelanggan',
                            'cashier' => 'Input Kasir',
                            default => $state,
                        }),
                ])->columns(3),

            Section::make('Pembayaran')
                ->schema([
                    TextEntry::make('payment_method')->label('Metode')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'bayar_nanti' => 'Bayar Nanti',
                            default => '-',
                        }),
                    TextEntry::make('total_amount')->label('Total')->money('IDR'),
                    TextEntry::make('is_paid')->label('Status Bayar')
                        ->badge()
                        ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Lunas' : 'Belum Bayar'),
                    TextEntry::make('status')->label('Status Pesanan')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'diproses' => 'info',
                            'selesai' => 'success',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Pending',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai',
                            default => $state,
                        }),
                ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->default('Guest'),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->searchable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Lunas')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai'  => 'Selesai',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Bayar')
                    ->options([
                        'cash'        => 'Tunai',
                        'qris'        => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}
