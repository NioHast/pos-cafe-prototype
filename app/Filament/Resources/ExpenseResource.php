<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-trending-down';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Expenses';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldCollapsedNavigationGroup = true;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Expense Information')
                    ->schema([
                        Components\TextInput::make('vendor')
                            ->label('Vendor')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Supplier Name'),
                        Components\Select::make('category')
                            ->label('Category')
                            ->required()
                            ->options([
                                'Inventory' => 'Inventory',
                                'Utilities' => 'Utilities',
                                'Salary' => 'Salary',
                                'Rent' => 'Rent',
                                'Marketing' => 'Marketing',
                                'Other' => 'Other',
                            ])
                            ->native(false),
                        Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank Transfer' => 'Bank Transfer',
                                'Credit Card' => 'Credit Card',
                                'Debit Card' => 'Debit Card',
                                'Other' => 'Other',
                            ])
                            ->native(false),
                        Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Inventory' => 'warning',
                        'Utilities' => 'info',
                        'Salary' => 'success',
                        'Rent' => 'danger',
                        'Marketing' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'Inventory' => 'Inventory',
                        'Utilities' => 'Utilities',
                        'Salary' => 'Salary',
                        'Rent' => 'Rent',
                        'Marketing' => 'Marketing',
                        'Other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank Transfer' => 'Bank Transfer',
                        'Credit Card' => 'Credit Card',
                        'Debit Card' => 'Debit Card',
                        'Other' => 'Other',
                    ]),
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
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
