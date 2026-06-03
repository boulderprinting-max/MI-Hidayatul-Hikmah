<?php

namespace App\Filament\Resources\RaporNilaiResource\Pages;

use App\Filament\Resources\RaporNilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRaporNilai extends EditRecord
{
    protected static string $resource = RaporNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
