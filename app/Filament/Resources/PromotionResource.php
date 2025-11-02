<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Promotions';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Promotion Information')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Promotion Name')
                            ->required()
                            ->maxLength(255),
                        Components\Select::make('type')
                            ->label('Promotion Type')
                            ->options([
                                'percentage' => 'Percentage Discount',
                                'fixed_amount' => 'Fixed Amount Discount',
                                'buy_x_get_y' => 'Buy X Get Y',
                                'bundle' => 'Bundle Deal',
                            ])
                            ->required()
                            ->default('percentage'),
                        Components\TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->prefix('Rp / %')
                            ->helperText('Enter percentage (e.g., 10 for 10%) or fixed amount'),
                        Components\TextInput::make('min_purchase')
                            ->label('Minimum Purchase')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable()
                            ->helperText('Leave empty if no minimum purchase required'),
                    ])->columns(2),

                Components\Section::make('Validity Period')
                    ->schema([
                        Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now()),
                        Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->after('start_date'),
                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ])->columns(3),

                Components\Section::make('Usage Limits & Details')
                    ->schema([
                        Components\TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->numeric()
                            ->nullable()
                            ->helperText('Maximum number of times this promotion can be used. Leave empty for unlimited'),
                        Components\TextInput::make('usage_count')
                            ->label('Usage Count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Components\Select::make('applicable_items')
                            ->label('Applicable Menu Items')
                            ->multiple()
                            ->relationship('', 'name', fn ($query) => $query->from('menu'))
                            ->preload()
                            ->searchable()
                            ->helperText('Select menu items this promotion applies to. Leave empty for all items')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Promotion Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'fixed_amount',
                        'warning' => 'buy_x_get_y',
                        'danger' => 'bundle',
                    ]),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(fn ($record) => $record->type === 'percentage' 
                        ? $record->discount_value . '%' 
                        : 'Rp ' . number_format($record->discount_value, 0, ',', '.')),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'expired',
                    ]),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->formatStateUsing(fn ($record) => 
                        $record->usage_limit 
                            ? "{$record->usage_count} / {$record->usage_limit}" 
                            : $record->usage_count
                    )
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                        'bundle' => 'Bundle',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'view' => Pages\ViewPromotion::route('/{record}'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }
}
