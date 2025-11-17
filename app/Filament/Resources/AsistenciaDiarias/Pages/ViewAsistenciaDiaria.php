<?php

namespace App\Filament\Resources\AsistenciaDiarias\Pages;

use App\Filament\Resources\AsistenciaDiarias\AsistenciaDiariaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAsistenciaDiaria extends ViewRecord
{
    protected static string $resource = AsistenciaDiariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
