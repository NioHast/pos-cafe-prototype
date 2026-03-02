<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Components;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'studentProfile';

    protected static ?string $title = 'Student Profile';

    public static function canViewForRecord(
        \Illuminate\Database\Eloquent\Model $ownerRecord,
        string $pageClass,
    ): bool {
        return $ownerRecord->hasRole('student');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\TextInput::make('nim')
                    ->label('NIM (Nomor Induk Mahasiswa)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Components\TextInput::make('faculty')
                    ->label('Faculty')
                    ->required()
                    ->maxLength(255),
                Components\TextInput::make('major')
                    ->label('Major')
                    ->required()
                    ->maxLength(255),
                Components\TextInput::make('year')
                    ->label('Enrollment Year')
                    ->required()
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(now()->year),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('faculty')
                    ->label('Faculty'),
                Tables\Columns\TextColumn::make('major')
                    ->label('Major'),
                Tables\Columns\TextColumn::make('year')
                    ->label('Year'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }
}
