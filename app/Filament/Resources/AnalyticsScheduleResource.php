<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsScheduleResource\Pages;
use App\Models\AnalyticsSchedule;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnalyticsScheduleResource extends Resource
{
    protected static ?string $model = AnalyticsSchedule::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Analytics Schedule';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Schedule Information')
                    ->schema([
                        Components\TextInput::make('job_name')
                            ->label('Job Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., FP-Growth Analysis'),
                        Components\Select::make('frequency')
                            ->label('Frequency')
                            ->required()
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->native(false),
                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'failed' => 'Failed',
                            ])
                            ->default('active')
                            ->native(false),
                    ])->columns(3),

                Components\Section::make('Schedule Timing')
                    ->schema([
                        Components\DateTimePicker::make('last_run')
                            ->label('Last Run')
                            ->disabled()
                            ->displayFormat('d M Y, H:i'),
                        Components\DateTimePicker::make('next_run')
                            ->label('Next Run')
                            ->required()
                            ->default(now()->addDay())
                            ->displayFormat('d M Y, H:i'),
                    ])->columns(2),

                Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_name')
                    ->label('Job Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequency')
                    ->label('Frequency')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'daily' => 'success',
                        'weekly' => 'info',
                        'monthly' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_run')
                    ->label('Last Run')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('next_run')
                    ->label('Next Run')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->shouldRun() ? 'warning' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('frequency')
                    ->label('Frequency')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
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
            ->defaultSort('next_run', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsSchedules::route('/'),
            'create' => Pages\CreateAnalyticsSchedule::route('/create'),
            'view' => Pages\ViewAnalyticsSchedule::route('/{record}'),
            'edit' => Pages\EditAnalyticsSchedule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'failed')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
