<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms\Components;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static bool $shouldCollapsedNavigationGroup = true;

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 1;

    /**
     * Orders are created by Kasir UI, not from admin panel.
     * Form is only used for the Void action modal.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    /**
     * Infolist for the ViewOrder page — read-only order details.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Order Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('d M Y, H:i:s'),
                        TextEntry::make('cashier.name')
                            ->label('Cashier'),
                    ])->columns(3),

                SchemaComponents\Section::make('Customer')
                    ->schema([
                        TextEntry::make('customer_display_name')
                            ->label('Name'),
                        TextEntry::make('customer_type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'student' => 'info',
                                default => 'gray',
                            }),
                    ])->columns(2),

                SchemaComponents\Section::make('Financial Summary')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR'),
                        TextEntry::make('discount_total')
                            ->label('Discount')
                            ->money('IDR'),
                        TextEntry::make('tax_amount')
                            ->label('Tax')
                            ->money('IDR'),
                        TextEntry::make('grand_total')
                            ->label('Grand Total')
                            ->money('IDR')
                            ->weight('bold'),
                    ])->columns(4),

                SchemaComponents\Section::make('Payment')
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Method'),
                        TextEntry::make('payment_status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'gray',
                                default => 'gray',
                            }),
                    ])->columns(2),

                SchemaComponents\Section::make('Void Information')
                    ->schema([
                        TextEntry::make('void_reason')
                            ->label('Reason'),
                        TextEntry::make('void_notes')
                            ->label('Notes'),
                        TextEntry::make('voided_at')
                            ->label('Voided At')
                            ->dateTime('d M Y, H:i:s'),
                        TextEntry::make('voidedByUser.name')
                            ->label('Voided By'),
                    ])->columns(2)
                    ->visible(fn (Order $record): bool => $record->isVoided()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->id)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label('Customer')
                    ->searchable('customer_name'),
                Tables\Columns\BadgeColumn::make('customer_type')
                    ->label('Type')
                    ->colors([
                        'info' => 'student',
                        'gray' => 'guest',
                    ]),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('voided_at')
                    ->label('Voided')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('')
                    ->trueColor('danger')
                    ->getStateUsing(fn ($record) => $record->isVoided()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('customer_type')
                    ->label('Customer Type')
                    ->options([
                        'student' => 'Student',
                        'guest' => 'Guest',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
                Tables\Filters\Filter::make('voided')
                    ->label('Voided Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('voided_at'))
                    ->toggle(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Void Transaction')
                    ->modalDescription('This action cannot be undone. The order will be marked as voided.')
                    ->form([
                        Components\Select::make('void_reason')
                            ->label('Reason')
                            ->options([
                                'customer_cancel' => 'Customer Cancel',
                                'wrong_order' => 'Wrong Order',
                                'payment_failed' => 'Payment Failed',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Components\Textarea::make('void_notes')
                            ->label('Notes')
                            ->placeholder('Detail tambahan...'),
                    ])
                    ->action(fn (Order $record, array $data) => $record->void($data))
                    ->visible(fn (Order $record): bool => !$record->isVoided()),
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
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    /**
     * Today's order count as navigation badge.
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}
