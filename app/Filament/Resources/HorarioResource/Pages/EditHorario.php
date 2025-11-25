<?php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditHorario extends EditRecord
{
    protected static string $resource = HorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver_empleados')
                ->label('Ver Empleados Asignados')
                ->icon('heroicon-o-user-group')
                ->color('info')
                ->url(fn() => route('filament.admin.resources.empleados.index', [
                    'tableFilters' => ['horario_id' => ['value' => $this->record->id]]
                ]))
                ->visible(fn() => $this->record->empleados()->count() > 0),

            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Horario')
                ->modalDescription('¿Estás seguro de que deseas eliminar este horario? Esta acción no se puede deshacer.')
                ->successNotificationTitle('Horario eliminado')
                ->before(function () {
                    // Verificar si hay empleados asignados
                    if ($this->record->empleados()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar')
                            ->body('Este horario tiene empleados asignados. Debes reasignarlos antes de eliminarlo.')
                            ->persistent()
                            ->send();
                        
                        $this->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Horario actualizado')
            ->body('Los cambios han sido guardados exitosamente.');
    }

    public function getTitle(): string
    {
        return 'Editar Horario: ' . $this->record->nombre;
    }

    public function getSubheading(): ?string
    {
        $empleadosCount = $this->record->empleados()->count();
        return $empleadosCount > 0 
            ? "Este horario está asignado a {$empleadosCount} empleado(s)"
            : 'Este horario no tiene empleados asignados';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Log para debug
        logger()->info('[EditHorario] mutateFormDataBeforeFill', [
            'dias_laborables_raw' => $data['dias_laborables'] ?? 'NOT_SET',
            'dias_laborables_type' => gettype($data['dias_laborables'] ?? null)
        ]);

        // Convertir los días laborables desde JSON a array para el formulario
        if (isset($data['dias_laborables'])) {
            if (is_string($data['dias_laborables'])) {
                $dias = json_decode($data['dias_laborables'], true);
            } else {
                $dias = $data['dias_laborables'];
            }

            if (is_array($dias)) {
                // Convertir de formato asociativo ['lunes' => true] a array simple ['lunes']
                $data['dias_laborables'] = array_keys(array_filter($dias, function($value) {
                    return $value === true || $value === 1 || $value === '1';
                }));
            }
        }

        logger()->info('[EditHorario] mutateFormDataBeforeFill RESULT', [
            'dias_laborables_converted' => $data['dias_laborables'] ?? 'NOT_SET'
        ]);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Log para debug
        logger()->info('[EditHorario] mutateFormDataBeforeSave INPUT', [
            'dias_laborables_raw' => $data['dias_laborables'] ?? 'NOT_SET',
            'dias_laborables_type' => gettype($data['dias_laborables'] ?? null)
        ]);

        // Convertir los días laborables a formato JSON si vienen como array
        if (isset($data['dias_laborables'])) {
            // Si viene como array, procesarlo
            if (is_array($data['dias_laborables'])) {
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
            // Si ya es un objeto/array asociativo (desde la BD), dejarlo como está
            elseif (is_object($data['dias_laborables']) || is_string($data['dias_laborables'])) {
                // No hacer nada, ya está en el formato correcto
            }
        } else {
            // Si no viene el campo, usar valores por defecto (ningún día)
            $data['dias_laborables'] = [
                'lunes' => false,
                'martes' => false,
                'miercoles' => false,
                'jueves' => false,
                'viernes' => false,
                'sabado' => false,
                'domingo' => false,
            ];
        }

        logger()->info('[EditHorario] mutateFormDataBeforeSave OUTPUT', [
            'dias_laborables_converted' => $data['dias_laborables']
        ]);

        return $data;
    }
}
