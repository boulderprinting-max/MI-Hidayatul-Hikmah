<?php

namespace App\Filament\Portal\Resources\PengumumanResource\Pages;

use App\Filament\Portal\Resources\PengumumanResource;
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
