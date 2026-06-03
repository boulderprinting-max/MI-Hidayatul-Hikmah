<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TugasSubmissionResource\Pages;
use App\Filament\Resources\TugasSubmissionResource\RelationManagers;
use App\Models\TugasSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TugasSubmissionResource extends Resource
{
    protected static ?string $model = TugasSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tugas_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('siswa_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('file_jawaban'),
                Forms\Components\Textarea::make('catatan_siswa')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nilai')
                    ->numeric(),
                Forms\Components\Textarea::make('catatan_guru')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('submitted_at'),
                Forms\Components\DateTimePicker::make('graded_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tugas_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswa_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_jawaban')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('graded_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListTugasSubmissions::route('/'),
            'create' => Pages\CreateTugasSubmission::route('/create'),
            'edit' => Pages\EditTugasSubmission::route('/{record}/edit'),
        ];
    }
}
