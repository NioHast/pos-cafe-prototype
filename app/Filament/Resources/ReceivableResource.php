<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ReceivableResource\Pages\ListReceivables;
use App\Filament\Resources\ReceivableResource\Pages\CreateReceivable;
use App\Filament\Resources\ReceivableResource\Pages\EditReceivable;
use App\Filament\Resources\ReceivableResource\Pages;
use App\Models\Receivable;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReceivableResource extends Resource
{
    protected static ?string $model = Receivable::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Receivables';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('customer_name')
                ->label('Customer Name')
                ->required()
                ->maxLength(255),
            TextInput::make('amount')
                ->label('Total Amount')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),
            DatePicker::make('invoice_date')
                ->label('Invoice Date')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('due_date')
                ->label('Due Date')
                ->required()
                ->default(now()->addDays(30))
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Receivable::STATUS_PENDING => 'Pending',
                    Receivable::STATUS_PARTIAL => 'Partial',
                    Receivable::STATUS_PAID => 'Paid',
                    Receivable::STATUS_OVERDUE => 'Overdue',
                ])
                ->required()
                ->default(Receivable::STATUS_PENDING)
                ->native(false),
            TextInput::make('paid_amount')
                ->label('Paid Amount')
                ->required()
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('Rp'),
            Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Receivable $record): ?string => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Receivable::STATUS_PAID => 'success',
                        Receivable::STATUS_PARTIAL => 'warning',
                        Receivable::STATUS_OVERDUE => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Receivable::STATUS_PENDING => 'Pending',
                        Receivable::STATUS_PARTIAL => 'Partial',
                        Receivable::STATUS_PAID => 'Paid',
                        Receivable::STATUS_OVERDUE => 'Overdue',
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
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivables::route('/'),
            'create' => CreateReceivable::route('/create'),
            'edit' => EditReceivable::route('/{record}/edit'),
        ];
    }
}