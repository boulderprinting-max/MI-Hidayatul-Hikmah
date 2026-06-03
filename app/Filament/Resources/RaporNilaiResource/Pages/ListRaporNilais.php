<?php

namespace App\Filament\Resources\RaporNilaiResource\Pages;

use App\Filament\Resources\RaporNilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRaporNilais extends ListRecords
{
    protected static string $resource = RaporNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
