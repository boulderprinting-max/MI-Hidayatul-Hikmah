<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\NilaiResource\Pages;
use App\Filament\Guru\Resources\NilaiResource\RelationManagers;
use App\Models\Nilai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class NilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Nilai Siswa';
    protected static ?string $modelLabel = 'Nilai';
    protected static ?string $pluralModelLabel = 'Nilai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('siswa_id')
                            ->relationship('siswa', 'nama_lengkap')
                            ->searchable()
                            ->required()
                            ->label('Siswa'),
                        Forms\Components\Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required()
                            ->label('Kelas'),
                        Forms\Components\Select::make('mapel_id')
                            ->relationship('mapel', 'nama')
                            ->required()
                            ->label('Mata Pelajaran'),
                        Forms\Components\Select::make('tahun_ajaran_id')
                            ->relationship('tahunAjaran', 'nama', function (Builder $query) {
                                return $query->where('is_active', true);
                            })
                            ->required()
                            ->label('Tahun Ajaran'),
                        Forms\Components\Select::make('jenis')
                            ->options([
                                'tugas' => 'Tugas',
                                'uh' => 'Ulangan Harian',
                                'uts' => 'Ujian Tengah Semester',
                                'uas' => 'Ujian Akhir Semester',
                            ])
                            ->required()
                            ->label('Jenis Penilaian'),
                        Forms\Components\TextInput::make('nilai')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->label('Nilai'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('guru_id')
                            ->default(fn () => Auth::user()->guru->id ?? null),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama')
                    ->label('Mata Pelajaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tugas' => 'Tugas',
                        'uh' => 'UH',
                        'uts' => 'UTS',
                        'uas' => 'UAS',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahunAjaran.nama')
                    ->label('T.A.')
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
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->label('Kelas'),
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->relationship('mapel', 'nama')
                    ->label('Mata Pelajaran'),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'tugas' => 'Tugas',
                        'uh' => 'Ulangan Harian',
                        'uts' => 'Ujian Tengah Semester',
                        'uas' => 'Ujian Akhir Semester',
                    ])
                    ->label('Jenis Penilaian'),
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
            'index' => Pages\ListNilais::route('/'),
            'create' => Pages\CreateNilai::route('/create'),
            'edit' => Pages\EditNilai::route('/{record}/edit'),
        ];
    }
}
