<?php

namespace App\Filament\Resources\Alarmas\Pages;

use App\Filament\Resources\Alarmas\AlarmaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAlarma extends EditRecord
{
    protected static string $resource = AlarmaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
