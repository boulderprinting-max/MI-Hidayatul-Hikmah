<?php

namespace App\Filament\Resources\PengaturanSekolahResource\Pages;

use App\Filament\Resources\PengaturanSekolahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanSekolah extends EditRecord
{
    protected static string $resource = PengaturanSekolahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
