<?php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateHorario extends CreateRecord
{
    protected static string $resource = HorarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Horario creado')
            ->body('El horario ha sido creado exitosamente.');
    }

    public function getTitle(): string
    {
        return 'Crear Nuevo Horario';
    }

    public function getSubheading(): ?string
    {
        return 'Define un nuevo turno de trabajo para el personal';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convertir los días laborables a formato JSON si vienen como array
        if (isset($data['dias_laborables']) && is_array($data['dias_laborables'])) {
            $diasMap = [
                'lunes' => in_array('lunes', $data['dias_laborables']),
                'martes' => in_array('martes', $data['dias_laborables']),
                'miercoles' => in_array('miercoles', $data['dias_laborables']),
                'jueves' => in_array('jueves', $data['dias_laborables']),
                'viernes' => in_array('viernes', $data['dias_laborables']),
                'sabado' => in_array('sabado', $data['dias_laborables']),
                'domingo' => in_array('domingo', $data['dias_laborables']),
            ];
            $data['dias_laborables'] = $diasMap;
        }

        return $data;
    }
}
