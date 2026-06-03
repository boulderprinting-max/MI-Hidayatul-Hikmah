<?php

namespace App\Filament\Resources\WaliMuridResource\Pages;

use App\Filament\Resources\WaliMuridResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaliMurids extends ListRecords
{
    protected static string $resource = WaliMuridResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
