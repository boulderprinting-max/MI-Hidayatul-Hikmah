<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaporNilaiResource\Pages;
use App\Filament\Resources\RaporNilaiResource\RelationManagers;
use App\Models\RaporNilai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RaporNilaiResource extends Resource
{
    protected static ?string $model = RaporNilai::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rapor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('mapel_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nilai_pengetahuan')
                    ->numeric(),
                Forms\Components\TextInput::make('nilai_keterampilan')
                    ->numeric(),
                Forms\Components\TextInput::make('predikat'),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rapor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_pengetahuan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_keterampilan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('predikat')
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
            'index' => Pages\ListRaporNilais::route('/'),
            'create' => Pages\CreateRaporNilai::route('/create'),
            'edit' => Pages\EditRaporNilai::route('/{record}/edit'),
        ];
    }
}
