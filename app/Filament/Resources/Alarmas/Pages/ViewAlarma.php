<?php

namespace App\Filament\Resources\Alarmas\Pages;

use App\Filament\Resources\Alarmas\AlarmaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAlarma extends ViewRecord
{
    protected static string $resource = AlarmaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
