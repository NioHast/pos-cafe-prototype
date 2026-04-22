<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\DemoAdminData;
use App\Support\DemoAdminMode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Pengguna';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create')
                ->maxLength(255),
            Select::make('role')
                ->label('Role')
                ->options([
                    'admin'    => 'Admin',
                    'cashier'  => 'Kasir',
                    'customer' => 'Pelanggan',
                ])
                ->required()
                ->default('customer'),
            TextInput::make('phone')
                ->label('No. HP')
                ->nullable()
                ->maxLength(20),
        ]);
    }

    public static function table(Table $table): Table
    {
        if (DemoAdminMode::enabled()) {
            return $table
                ->columns([
                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('email')
                        ->label('Email')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('role')
                        ->label('Role')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'admin' => 'success',
                            'cashier' => 'info',
                            'customer' => 'warning',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'admin' => 'Admin',
                            'cashier' => 'Kasir',
                            'customer' => 'Pelanggan',
                            default => $state,
                        })
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->label('Terdaftar')
                        ->dateTime('d M Y')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->records(fn (?string $search = null, ?string $sortColumn = null, ?string $sortDirection = null, int | string $page = 1, int | string $recordsPerPage = 10) => DemoAdminData::forTable('users', $search, $sortColumn, $sortDirection, $page, $recordsPerPage))
                ->filters([])
                ->recordActions([])
                ->toolbarActions([])
                ->defaultSort('created_at', 'desc');
        }

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin'    => 'success',
                        'cashier'  => 'info',
                        'customer' => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin'    => 'Admin',
                        'cashier'  => 'Kasir',
                        'customer' => 'Pelanggan',
                        default    => $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'cashier'  => 'Kasir',
                        'customer' => 'Pelanggan',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        if (DemoAdminMode::enabled()) {
            return [
                'index' => ListUsers::route('/'),
            ];
        }

        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
