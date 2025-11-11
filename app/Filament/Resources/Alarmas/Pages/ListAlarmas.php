<?php

namespace App\Filament\Resources\Alarmas\Pages;

use App\Filament\Resources\Alarmas\AlarmaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlarmas extends ListRecords
{
    protected static string $resource = AlarmaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
