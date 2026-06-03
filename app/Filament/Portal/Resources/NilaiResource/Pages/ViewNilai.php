<?php

namespace App\Filament\Portal\Resources\NilaiResource\Pages;

use App\Filament\Portal\Resources\NilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNilai extends ViewRecord
{
    protected static string $resource = NilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
