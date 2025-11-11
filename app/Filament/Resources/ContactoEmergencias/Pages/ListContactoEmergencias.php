<?php

namespace App\Filament\Resources\ContactoEmergencias\Pages;

use App\Filament\Resources\ContactoEmergencias\ContactoEmergenciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactoEmergencias extends ListRecords
{
    protected static string $resource = ContactoEmergenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
