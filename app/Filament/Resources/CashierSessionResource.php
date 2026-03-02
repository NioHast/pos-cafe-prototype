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

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';
    
    protected static bool $shouldCollapsedNavigationGroup = true;

    protected static ?string $navigationLabel = 'Cashier Sessions';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Session Information')
                    ->schema([
                        Components\Select::make('user_id')
                            ->label('Cashier')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Components\DateTimePicker::make('shift_start')
                            ->label('Start Time')
                            ->required()
                            ->default(now()),
                        Components\DateTimePicker::make('shift_end')
                            ->label('End Time')
                            ->nullable(),
                    ])->columns(3),

                SchemaComponents\Section::make('Statistics')
                    ->schema([
                        Components\TextInput::make('total_sales')
                            ->label('Total Sales')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Components\TextInput::make('total_transactions')
                            ->label('Total Transactions')
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
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift_start')
                    ->label('Shift Start')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift_end')
                    ->label('Shift End')
                    ->dateTime('d M Y, H:i')
                    ->default('-')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->isActive() ? 'Active' : 'Completed')
                    ->colors([
                        'success' => 'Active',
                        'secondary' => 'Completed',
                    ]),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Transactions')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration (hours)')
                    ->getStateUsing(fn ($record) => $record->duration ? number_format($record->duration, 1) : '-')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Active Sessions')
                    ->query(fn ($query) => $query->active()),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
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

    // Disabled for performance optimization
    // public static function getNavigationBadge(): ?string
    // {
    //     return cache()->remember('cashier_sessions_active_count', 300, function () {
    //         return static::getModel()::active()->count() ?: null;
    //     });
    // }
}
