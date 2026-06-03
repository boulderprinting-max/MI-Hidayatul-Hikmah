<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanSekolahResource\Pages;
use App\Filament\Resources\PengaturanSekolahResource\RelationManagers;
use App\Models\PengaturanSekolah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengaturanSekolahResource extends Resource
{
    protected static ?string $model = PengaturanSekolah::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_sekolah')
                    ->required(),
                Forms\Components\TextInput::make('nss'),
                Forms\Components\TextInput::make('npsn'),
                Forms\Components\Textarea::make('alamat')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('telepon')
                    ->tel(),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('website'),
                Forms\Components\TextInput::make('kepala_sekolah'),
                Forms\Components\TextInput::make('nip_kepala_sekolah'),
                Forms\Components\TextInput::make('logo'),
                Forms\Components\TextInput::make('kop_surat'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nss')
                    ->searchable(),
                Tables\Columns\TextColumn::make('npsn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kepala_sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nip_kepala_sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kop_surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanSekolahs::route('/'),
            'create' => Pages\CreatePengaturanSekolah::route('/create'),
            'edit' => Pages\EditPengaturanSekolah::route('/{record}/edit'),
        ];
    }
}
