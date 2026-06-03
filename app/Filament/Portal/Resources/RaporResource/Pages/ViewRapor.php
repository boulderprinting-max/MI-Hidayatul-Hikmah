<?php

namespace App\Filament\Portal\Resources\RaporResource\Pages;

use App\Filament\Portal\Resources\RaporResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRapor extends ViewRecord
{
    protected static string $resource = RaporResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
