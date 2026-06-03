<?php

namespace App\Filament\Guru\Resources\PengumumanResource\Pages;

use App\Filament\Guru\Resources\PengumumanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengumumen extends ListRecords
{
    protected static string $resource = PengumumanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
