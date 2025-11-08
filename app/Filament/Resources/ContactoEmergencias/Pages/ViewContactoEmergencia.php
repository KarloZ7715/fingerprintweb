<?php

namespace App\Filament\Resources\ContactoEmergencias\Pages;

use App\Filament\Resources\ContactoEmergencias\ContactoEmergenciaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactoEmergencia extends ViewRecord
{
    protected static string $resource = ContactoEmergenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
