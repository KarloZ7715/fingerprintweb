<?php

namespace App\Filament\Resources\AsistenciaDiarias\Pages;

use App\Filament\Resources\AsistenciaDiarias\AsistenciaDiariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAsistenciaDiarias extends ListRecords
{
    protected static string $resource = AsistenciaDiariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
