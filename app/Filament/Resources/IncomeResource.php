<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
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
    protected static bool $shouldCollapsedNavigationGroup = true;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Income Information')
                    ->schema([
                        FormComponents\TextInput::make('source')
                            ->label('Source')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Sales, Services, Other'),
                        FormComponents\Select::make('category')
                            ->label('Category')
                            ->required()
                            ->options([
                                'Sales' => 'Sales',
                                'Services' => 'Services',
                                'Investment' => 'Investment',
                                'Other' => 'Other',
                            ])
                            ->native(false),
                        FormComponents\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        FormComponents\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        FormComponents\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500),
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
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Sales' => 'success',
                        'Services' => 'info',
                        'Investment' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
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
                        'Sales' => 'Sales',
                        'Services' => 'Services',
                        'Investment' => 'Investment',
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'view' => Pages\ViewIncome::route('/{record}'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
