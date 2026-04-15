<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use App\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use App\Filament\Resources\ExpenseResource\Pages\EditExpense;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('vendor')
                ->label('Vendor')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('Category')
                ->options([
                    'inventory' => 'Inventory',
                    'utilities' => 'Utilities',
                    'salary' => 'Salary',
                    'rent' => 'Rent',
                    'marketing' => 'Marketing',
                    'other' => 'Other',
                ])
                ->required()
                ->searchable()
                ->native(false),
            TextInput::make('amount')
                ->label('Amount')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),
            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->default(now())
                ->native(false),
            Select::make('payment_method')
                ->label('Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'debit_card' => 'Debit Card',
                    'other' => 'Other',
                ])
                ->searchable()
                ->native(false)
                ->nullable(),
            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'inventory' => 'warning',
                        'utilities' => 'info',
                        'salary' => 'success',
                        'rent' => 'danger',
                        'marketing' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst(str_replace('_', ' ', $state)) : '-')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'inventory' => 'Inventory',
                        'utilities' => 'Utilities',
                        'salary' => 'Salary',
                        'rent' => 'Rent',
                        'marketing' => 'Marketing',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'other' => 'Other',
                    ]),
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
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}