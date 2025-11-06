<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use App\Models\Huella;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

class CreateEmpleado extends CreateRecord
{
    protected static string $resource = EmpleadoResource::class;

    /**
     * Personalizamos las acciones del formulario:
     * - Guardar empleado
     * - Capturar huella (ESP32)
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCaptureFingerprintAction(),
        ];
    }

    /**
     * Acción personalizada para comunicarse con el ESP32
     */
    protected function getCaptureFingerprintAction()
    {
        return \Filament\Forms\Components\Actions\Action::make('capturarHuella')
            ->label('Capturar Huella')
            ->icon('heroicon-o-finger-print')
            ->color('primary')
            ->action(function () {
                try {
                    // ⚠️ Cambia esta IP por la de tu ESP32
                    $response = Http::timeout(10)->get('http://192.168.4.1/api/fingerprint/capture');

                    if ($response->failed()) {
                        Notification::make()
                            ->title('No se pudo conectar con el lector de huellas')
                            ->danger()
                            ->send();
                        return;
                    }

                    $fingerprintId = $response->json('fingerprint_id') ?? $response->json('codigo_huella');

                    if (!$fingerprintId) {
                        Notification::make()
                            ->title('No se recibió el ID de huella')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Guardamos el ID de huella temporal en el formulario (sin BD aún)
                    $this->form->fill([
                        ...$this->form->getState(),
                        'fingerprint_id' => $fingerprintId,
                    ]);

                    Notification::make()
                        ->title("Huella capturada (ID: {$fingerprintId})")
                        ->success()
                        ->send();

                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Error al conectar con el lector')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Antes de crear el empleado en BD, guardamos el ID de huella temporal
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->fingerprintId = $data['fingerprint_id'] ?? null;
        unset($data['fingerprint_id']);
        return $data;
    }

    /**
     * Después de crear el empleado, registramos la huella en la tabla `huella`
     */
    protected function afterCreate(): void
    {
        if (!empty($this->fingerprintId)) {
            Huella::create([
                'codigo_huella' => $this->fingerprintId,
                'empleado_id' => $this->record->id,
                'fecha_enrolamiento' => Carbon::now(),
                'estado' => 'activa',
            ]);

            Notification::make()
                ->title('Empleado y huella registrados correctamente')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Empleado registrado sin huella')
                ->warning()
                ->send();
        }
    }
}
