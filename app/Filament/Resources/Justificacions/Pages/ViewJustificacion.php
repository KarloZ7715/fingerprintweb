<?php

namespace App\Filament\Resources\Justificacions\Pages;

use App\Filament\Resources\Justificacions\JustificacionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJustificacion extends ViewRecord
{
    protected static string $resource = JustificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
