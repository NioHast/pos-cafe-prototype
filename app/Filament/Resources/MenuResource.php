<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Menu';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama Menu')
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(2)
                ->nullable(),
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('price')
                ->label('Harga Normal')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),
            TextInput::make('cashback')
                ->label('Cashback Mahasiswa')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('Rp'),
            Toggle::make('is_available')
                ->label('Tersedia')
                ->default(true)
                ->inline(false),
            Toggle::make('is_student_discount')
                ->label('Ada Diskon Mahasiswa')
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cashback')
                    ->label('Cashback')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_student_discount')
                    ->label('Diskon Mhs')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Tersedia')
                    ->placeholder('Semua')
                    ->trueLabel('Tersedia')
                    ->falseLabel('Tidak Tersedia'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit'   => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
