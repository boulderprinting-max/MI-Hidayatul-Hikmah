<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaporResource\Pages;
use App\Filament\Resources\RaporResource\RelationManagers;
use App\Models\Rapor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RaporResource extends Resource
{
    protected static ?string $model = Rapor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('siswa_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('kelas_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tahun_ajaran_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('semester')
                    ->required(),
                Forms\Components\TextInput::make('total_hadir')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_izin')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_sakit')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_alfa')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('catatan_wali_kelas')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('catatan_kepala_sekolah')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ranking')
                    ->numeric(),
                Forms\Components\Toggle::make('is_published')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_ajaran_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_hadir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_izin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_sakit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_alfa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ranking')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
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
            'index' => Pages\ListRapors::route('/'),
            'create' => Pages\CreateRapor::route('/create'),
            'edit' => Pages\EditRapor::route('/{record}/edit'),
        ];
    }
}
