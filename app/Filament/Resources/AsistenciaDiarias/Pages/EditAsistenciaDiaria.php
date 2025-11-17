<?php

namespace App\Filament\Resources\AsistenciaDiarias\Pages;

use App\Filament\Resources\AsistenciaDiarias\AsistenciaDiariaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAsistenciaDiaria extends EditRecord
{
    protected static string $resource = AsistenciaDiariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
