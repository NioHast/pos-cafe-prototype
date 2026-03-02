<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentProfileResource\Pages;
use App\Models\StudentProfile;
use App\Models\Role;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentProfileResource extends Resource
{
    protected static ?string $model = StudentProfile::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Student Profiles';

    protected static ?string $modelLabel = 'Student Profile';

    protected static ?string $pluralModelLabel = 'Student Profiles';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('user_id')
                    ->label('User')
                    ->relationship(
                        'user',
                        'name',
                        fn ($query) => $query->whereHas('role', fn ($q) => $q->where('name', 'student'))
                    )
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->maxLength(255),
                        Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state)),
                        Components\Hidden::make('role_id')
                            ->default(fn () => Role::where('name', 'student')->first()?->id),
                    ])
                    ->helperText('Only users with "student" role are shown. You can create a new student user inline.'),
                Components\TextInput::make('nim')
                    ->label('NIM (Nomor Induk Mahasiswa)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->placeholder('e.g. 2024001'),
                Components\TextInput::make('faculty')
                    ->label('Faculty')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Fakultas Teknik'),
                Components\TextInput::make('major')
                    ->label('Major')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Teknik Informatika'),
                Components\TextInput::make('year')
                    ->label('Enrollment Year')
                    ->required()
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(now()->year)
                    ->placeholder(now()->year),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('faculty')
                    ->label('Faculty')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('major')
                    ->label('Major')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('faculty')
                    ->options(fn () => StudentProfile::query()
                        ->distinct()
                        ->pluck('faculty', 'faculty')
                        ->toArray()
                    )
                    ->label('Faculty'),
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => StudentProfile::query()
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->toArray()
                    )
                    ->label('Enrollment Year'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentProfiles::route('/'),
            'create' => Pages\CreateStudentProfile::route('/create'),
            'edit' => Pages\EditStudentProfile::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nim', 'faculty', 'major', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'NIM' => $record->nim,
            'Student' => $record->user?->name ?? '-',
            'Faculty' => $record->faculty,
        ];
    }
}
