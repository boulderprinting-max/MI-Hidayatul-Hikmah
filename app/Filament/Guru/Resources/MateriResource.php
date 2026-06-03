<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\MateriResource\Pages;
use App\Filament\Guru\Resources\MateriResource\RelationManagers;
use App\Models\Materi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MateriResource extends Resource
{
    protected static ?string $model = Materi::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Materi';
    protected static ?string $modelLabel = 'Materi';
    protected static ?string $pluralModelLabel = 'Materi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('deskripsi')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required(),
                        Forms\Components\Select::make('mapel_id')
                            ->relationship('mapel', 'nama')
                            ->required(),
                        Forms\Components\Select::make('tahun_ajaran_id')
                            ->relationship('tahunAjaran', 'nama', function (Builder $query) {
                                return $query->where('is_active', true);
                            })
                            ->required()
                            ->label('Tahun Ajaran'),
                        Forms\Components\Select::make('tipe_file')
                            ->options([
                                'document' => 'Dokumen (PDF, Word, dll)',
                                'video' => 'Video',
                                'link' => 'Link / URL',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\FileUpload::make('file_path')
                            ->directory('materi')
                            ->label('File')
                            ->visible(fn (callable $get) => in_array($get('tipe_file'), ['document', 'video'])),
                        Forms\Components\TextInput::make('youtube_url')
                            ->url()
                            ->label('Link/URL Youtube')
                            ->visible(fn (callable $get) => $get('tipe_file') === 'link'),
                        Forms\Components\Hidden::make('guru_id')
                            ->default(fn () => Auth::user()->guru->id ?? null),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(true)
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama')
                    ->label('Mata Pelajaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe_file')
                    ->label('Tipe File')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'document' => 'Dokumen',
                        'video' => 'Video',
                        'link' => 'Link',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Dipublikasi')
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
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->label('Kelas'),
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->relationship('mapel', 'nama')
                    ->label('Mata Pelajaran'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMateris::route('/'),
            'create' => Pages\CreateMateri::route('/create'),
            'edit' => Pages\EditMateri::route('/{record}/edit'),
        ];
    }
}
