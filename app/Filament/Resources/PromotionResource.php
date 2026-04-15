<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use App\Filament\Resources\PromotionResource\Pages\ListPromotions;
use App\Filament\Resources\PromotionResource\Pages\CreatePromotion;
use App\Filament\Resources\PromotionResource\Pages\EditPromotion;
use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Promotions';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Promotion Name')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('Promotion Type')
                ->options([
                    Promotion::TYPE_PERCENTAGE => 'Percentage Discount',
                    Promotion::TYPE_FIXED_AMOUNT => 'Fixed Amount Discount',
                    Promotion::TYPE_BUY_X_GET_Y => 'Buy X Get Y',
                    Promotion::TYPE_BUNDLE => 'Bundle',
                ])
                ->required()
                ->default(Promotion::TYPE_PERCENTAGE)
                ->native(false),
            TextInput::make('discount_value')
                ->label('Discount Value')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('Rp / %'),
            TextInput::make('min_purchase')
                ->label('Minimum Purchase')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->nullable(),
            DatePicker::make('start_date')
                ->label('Start Date')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('end_date')
                ->label('End Date')
                ->required()
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Promotion::STATUS_SCHEDULED => 'Scheduled',
                    Promotion::STATUS_ACTIVE => 'Active',
                    Promotion::STATUS_INACTIVE => 'Inactive',
                    Promotion::STATUS_EXPIRED => 'Expired',
                ])
                ->required()
                ->default(Promotion::STATUS_SCHEDULED)
                ->native(false),
            TextInput::make('usage_limit')
                ->label('Usage Limit')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->nullable(),
            TextInput::make('usage_count')
                ->label('Usage Count')
                ->numeric()
                ->integer()
                ->default(0)
                ->disabled()
                ->dehydrated(false),
            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
            Repeater::make('rules')
                ->relationship('rules')
                ->label('Applicability Rules')
                ->schema([
                    Select::make('applicable_type')
                        ->label('Rule Type')
                        ->options([
                            'menu' => 'Menu',
                            'category' => 'Category',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),
                    Select::make('applicable_id')
                        ->label('Target')
                        ->options(function (callable $get) {
                            $type = $get('applicable_type');

                            if ($type === 'category') {
                                return Category::query()->orderBy('name')->pluck('name', 'id')->all();
                            }

                            return Menu::query()->orderBy('name')->pluck('name', 'id')->all();
                        })
                        ->required()
                        ->searchable()
                        ->native(false),
                ])
                ->columns(2)
                ->defaultItems(0)
                ->addActionLabel('+ Add Rule')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Promotion Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Promotion::TYPE_PERCENTAGE => 'success',
                        Promotion::TYPE_FIXED_AMOUNT => 'info',
                        Promotion::TYPE_BUY_X_GET_Y => 'warning',
                        Promotion::TYPE_BUNDLE => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(function (Promotion $record): string {
                        if ($record->type === Promotion::TYPE_PERCENTAGE) {
                            return number_format((float) $record->discount_value, 2) . '%';
                        }

                        return 'Rp ' . number_format((float) $record->discount_value, 0, ',', '.');
                    }),
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Promotion::STATUS_SCHEDULED => 'warning',
                        Promotion::STATUS_ACTIVE => 'success',
                        Promotion::STATUS_INACTIVE => 'danger',
                        Promotion::STATUS_EXPIRED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('usage_count')
                    ->label('Used')
                    ->formatStateUsing(function (Promotion $record): string {
                        if ($record->usage_limit !== null) {
                            return $record->usage_count . ' / ' . $record->usage_limit;
                        }

                        return (string) $record->usage_count;
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Promotion::STATUS_SCHEDULED => 'Scheduled',
                        Promotion::STATUS_ACTIVE => 'Active',
                        Promotion::STATUS_INACTIVE => 'Inactive',
                        Promotion::STATUS_EXPIRED => 'Expired',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        Promotion::TYPE_PERCENTAGE => 'Percentage',
                        Promotion::TYPE_FIXED_AMOUNT => 'Fixed Amount',
                        Promotion::TYPE_BUY_X_GET_Y => 'Buy X Get Y',
                        Promotion::TYPE_BUNDLE => 'Bundle',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}