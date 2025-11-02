<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialReportResource\Pages;
use App\Models\FinancialReport;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialReportResource extends Resource
{
    protected static ?string $model = FinancialReport::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Periodic Reports';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Report Period')
                    ->schema([
                        Components\DatePicker::make('period_start')
                            ->label('Period Start')
                            ->required()
                            ->default(now()->startOfMonth()),
                        Components\DatePicker::make('period_end')
                            ->label('Period End')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->after('period_start'),
                    ])->columns(2),

                Components\Section::make('Financial Summary')
                    ->schema([
                        Components\TextInput::make('total_income')
                            ->label('Total Income')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Components\TextInput::make('total_expense')
                            ->label('Total Expense')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Components\TextInput::make('net_profit')
                            ->label('Net Profit')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Income - Expense'),
                    ])->columns(3),

                Components\DateTimePicker::make('generated_at')
                    ->label('Generated At')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Period Start')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Period End')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_income')
                    ->label('Total Income')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_expense')
                    ->label('Total Expense')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Net Profit')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->net_profit >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('profit_margin')
                    ->label('Profit Margin')
                    ->formatStateUsing(fn ($record) => number_format($record->profit_margin, 2) . '%')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('generated_at')
                    ->label('Generated At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn ($query) => $query->thisMonth()),
                Tables\Filters\Filter::make('last_month')
                    ->label('Last Month')
                    ->query(fn ($query) => $query->whereMonth('period_start', now()->subMonth()->month)),
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
            ->defaultSort('period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialReports::route('/'),
            'create' => Pages\CreateFinancialReport::route('/create'),
            'view' => Pages\ViewFinancialReport::route('/{record}'),
            'edit' => Pages\EditFinancialReport::route('/{record}/edit'),
        ];
    }
}
