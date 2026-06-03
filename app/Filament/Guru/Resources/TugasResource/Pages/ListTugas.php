<?php

namespace App\Filament\Guru\Resources\TugasResource\Pages;

use App\Filament\Guru\Resources\TugasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTugas extends ListRecords
{
    protected static string $resource = TugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
