<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateEmpleado extends CreateRecord
{
    protected static string $resource = EmpleadoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Empleado registrado exitosamente';
    }

    /**
     * Validación adicional antes de crear el empleado
     * Soporta números de teléfono internacionales
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validar email con @
        if (!str_contains($data['email'], '@')) {
            throw ValidationException::withMessages([
                'email' => 'El correo electrónico debe contener @',
            ]);
        }

        // Validar teléfono internacional (solo números después de limpiar)
        if (!ctype_digit($data['telefono'])) {
            throw ValidationException::withMessages([
                'telefono' => 'El teléfono debe contener solo números y el código de país',
            ]);
        }

        // Validar longitud razonable (códigos de país + número local)
        $length = strlen($data['telefono']);
        if ($length < 7 || $length > 15) {
            throw ValidationException::withMessages([
                'telefono' => 'El teléfono debe tener entre 7 y 15 dígitos (incluyendo código de país)',
            ]);
        }

        // Convertir campos opcionales vacíos a null explícitamente
        $data['segundo_nombre'] = $data['segundo_nombre'] ?? null;
        $data['horario_id'] = $data['horario_id'] ?? null;
        $data['foto_url'] = $data['foto_url'] ?? null;

        return $data;
    }
}
