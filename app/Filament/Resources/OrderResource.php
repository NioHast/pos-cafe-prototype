<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Support\DemoAdminData;
use App\Support\DemoAdminMode;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
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
        if (DemoAdminMode::enabled()) {
            return $table
                ->columns([
                    TextColumn::make('order_code')
                        ->label('Kode Pesanan')
                        ->searchable()
                        ->sortable()
                        ->copyable(),
                    TextColumn::make('customer_name')
                        ->label('Pelanggan')
                        ->searchable()
                        ->default('Guest'),
                    TextColumn::make('cashier.name')
                        ->label('Kasir')
                        ->searchable()
                        ->default('-'),
                    TextColumn::make('total_amount')
                        ->label('Total')
                        ->money('IDR')
                        ->sortable(),
                    TextColumn::make('payment_method')
                        ->label('Metode')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'bayar_nanti' => 'Bayar Nanti',
                            default => '-',
                        }),
                    TextColumn::make('status')
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
                    IconColumn::make('is_paid')
                        ->label('Lunas')
                        ->boolean(),
                    TextColumn::make('created_at')
                        ->label('Tanggal')
                        ->dateTime('d M Y, H:i')
                        ->sortable(),
                ])
                ->records(fn (?string $search = null, ?string $sortColumn = null, ?string $sortDirection = null, int | string $page = 1, int | string $recordsPerPage = 10) => DemoAdminData::forTable('orders', $search, $sortColumn, $sortDirection, $page, $recordsPerPage))
                ->filters([])
                ->recordActions([])
                ->toolbarActions([])
                ->defaultSort('created_at', 'desc');
        }

        return $table
            ->columns([
                TextColumn::make('order_code')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->default('Guest'),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                        default => '-',
                    }),
                TextColumn::make('status')
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
                IconColumn::make('is_paid')
                    ->label('Lunas')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai'  => 'Selesai',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Metode Bayar')
                    ->options([
                        'cash'        => 'Tunai',
                        'qris'        => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                    ]),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        if (DemoAdminMode::enabled()) {
            return [];
        }

        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        if (DemoAdminMode::enabled()) {
            return [
                'index' => ListOrders::route('/'),
            ];
        }

        return [
            'index' => ListOrders::route('/'),
            'view'  => ViewOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (DemoAdminMode::enabled()) {
            return DemoAdminData::navigationBadge('orders');
        }

        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}
