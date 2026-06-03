<?php

namespace App\Filament\Portal\Resources\TugasResource\Pages;

use App\Filament\Portal\Resources\TugasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTugas extends EditRecord
{
    protected static string $resource = TugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
