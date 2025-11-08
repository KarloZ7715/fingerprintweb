<?php

namespace App\Filament\Resources\ContactoEmergencias\Pages;

use App\Filament\Resources\ContactoEmergencias\ContactoEmergenciaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContactoEmergencia extends EditRecord
{
    protected static string $resource = ContactoEmergenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
