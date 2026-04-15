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
use App\Filament\Resources\IncomeResource\Pages\ListIncomes;
use App\Filament\Resources\IncomeResource\Pages\CreateIncome;
use App\Filament\Resources\IncomeResource\Pages\EditIncome;
use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Income';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('source')
                ->label('Source')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('Category')
                ->options([
                    'sales' => 'Sales',
                    'services' => 'Services',
                    'investment' => 'Investment',
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
                TextColumn::make('source')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'sales' => 'success',
                        'services' => 'info',
                        'investment' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
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
                        'sales' => 'Sales',
                        'services' => 'Services',
                        'investment' => 'Investment',
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
            'index' => ListIncomes::route('/'),
            'create' => CreateIncome::route('/create'),
            'edit' => EditIncome::route('/{record}/edit'),
        ];
    }
}