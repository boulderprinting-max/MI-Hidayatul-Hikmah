<?php

namespace App\Filament\Portal\Resources\JadwalResource\Pages;

use App\Filament\Portal\Resources\JadwalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwal extends ViewRecord
{
    protected static string $resource = JadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
