<?php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHorarios extends ListRecords
{
    protected static string $resource = HorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Horario')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí podrías agregar widgets de estadísticas si lo necesitas
        ];
    }

    public function getTitle(): string
    {
        return 'Gestión de Horarios';
    }

    public function getSubheading(): ?string
    {
        return 'Administra los turnos y horarios de trabajo del personal';
    }
}
