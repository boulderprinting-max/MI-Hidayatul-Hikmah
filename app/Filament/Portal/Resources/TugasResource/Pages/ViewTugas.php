<?php

namespace App\Filament\Portal\Resources\TugasResource\Pages;

use App\Filament\Portal\Resources\TugasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTugas extends ViewRecord
{
    protected static string $resource = TugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
