<?php

namespace App\Filament\Resources\PengaturanSekolahResource\Pages;

use App\Filament\Resources\PengaturanSekolahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanSekolahs extends ListRecords
{
    protected static string $resource = PengaturanSekolahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
