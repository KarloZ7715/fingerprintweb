<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpleado extends EditRecord
{
    protected static string $resource = EmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cambiar_estado')
                ->label('Cambiar Estado')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('estado')
                        ->label('Nuevo Estado')
                        ->options([
                            'Activo' => 'Activo',
                            'Inactivo' => 'Inactivo',
                            'Suspendido' => 'Suspendido',
                            'Vacaciones' => 'Vacaciones',
                        ])
                        ->default(fn() => $this->record->estado)
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['estado' => $data['estado']]);
                    $this->refreshFormData(['estado']);
                })
                ->successNotificationTitle('Estado actualizado correctamente')
                ->color('warning'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Empleado actualizado exitosamente';
    }

    /**
     * Validación adicional antes de guardar cambios
     * Soporta números de teléfono internacionales
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validar email con @ si existe
        if (!empty($data['email']) && !str_contains($data['email'], '@')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'El correo electrónico debe contener @',
            ]);
        }

        // Validar teléfono internacional (solo números después de limpiar) si existe
        if (!empty($data['telefono'])) {
            if (!ctype_digit($data['telefono'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'telefono' => 'El teléfono debe contener solo números y el código de país',
                ]);
            }

            // Validar longitud razonable (códigos de país + número local)
            $length = strlen($data['telefono']);
            if ($length < 7 || $length > 15) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'telefono' => 'El teléfono debe tener entre 7 y 15 dígitos (incluyendo código de país)',
                ]);
            }
        }

        // Convertir campos opcionales vacíos a null explícitamente
        $data['segundo_nombre'] = $data['segundo_nombre'] ?? null;
        $data['horario_id'] = $data['horario_id'] ?? null;
        $data['foto_url'] = $data['foto_url'] ?? null;

        return $data;
    }
}
