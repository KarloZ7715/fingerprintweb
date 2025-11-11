<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use App\Models\Empleado;
use App\Services\FingerprintService;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;

class EnrollFingerprint extends Page
{
    protected static string $resource = EmpleadoResource::class;

    protected string $view = 'filament.resources.empleado-resource.pages.enroll-fingerprint';

    public Empleado $record;

    // Variables de estado para el enrollment
    public string $enrollmentState = 'idle'; // idle, enrolling, success, error
    public int $enrollmentProgress = 0; // 0-100
    public string $enrollmentMessage = 'Listo para iniciar registro';
    public ?int $assignedSlotId = null;
    public bool $isPolling = false;

    /**
     * Título de la página
     */
    public function getTitle(): string
    {
        return 'Registrar Huella Dactilar';
    }

    /**
     * Subtítulo con nombre del empleado
     */
    public function getSubheading(): ?string
    {
        return "Empleado: {$this->record->nombre_completo} (Cédula: {$this->record->cedula})";
    }

    /**
     * Verificar que el empleado esté en estado correcto
     */
    public function mount(int|string $record): void
    {
        $this->record = Empleado::findOrFail($record);

        // Verificar que el empleado esté pendiente de huella
        if ($this->record->estado !== 'Pendiente_Huella' && !$this->record->tieneHuella()) {
            Notification::make()
                ->warning()
                ->title('Empleado ya tiene huella registrada')
                ->body('Este empleado ya cuenta con una huella activa.')
                ->send();

            $this->redirect(EmpleadoResource::getUrl('index'));
        }
    }

    /**
     * Método para iniciar el enrollment
     */
    public function startEnrollment(): void
    {
        $service = app(FingerprintService::class);

        // Verificar conexión con ESP32
        $connection = $service->checkEsp32Connection();

        if (!$connection['connected']) {
            Notification::make()
                ->danger()
                ->title('Sensor dactilar no disponible')
                ->body($connection['message'])
                ->persistent()
                ->send();

            return;
        }

        // Obtener slot disponible
        $availableSlot = $service->getAvailableSlot();

        if ($availableSlot === null) {
            Notification::make()
                ->danger()
                ->title('Sensor lleno')
                ->body('No hay slots disponibles en el sensor (300/300 usados)')
                ->persistent()
                ->send();

            return;
        }

        // Comunicar con ESP32 para iniciar enrollment
        try {
            $response = Http::timeout(10)->post(config('fingerprint.esp32_url') . '/fingerprint/start-enroll', [
                'empleado_id' => $this->record->id,
                'slot_id' => $availableSlot
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    $this->assignedSlotId = $availableSlot;
                    $this->enrollmentState = 'enrolling';
                    $this->enrollmentProgress = 5;
                    $this->enrollmentMessage = 'Coloque su dedo en el sensor...';
                    $this->isPolling = true;

                    Notification::make()
                        ->success()
                        ->title('Registro iniciado')
                        ->body("Slot #{$availableSlot} asignado. Siga las instrucciones del sensor.")
                        ->send();
                } else {
                    throw new \Exception($data['message'] ?? 'Error desconocido');
                }
            } else {
                throw new \Exception('El ESP32 respondió con error: ' . $response->status());
            }

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al iniciar registro')
                ->body('No se pudo comunicar con el ESP32: ' . $e->getMessage())
                ->persistent()
                ->send();

            $this->enrollmentState = 'error';
            $this->enrollmentMessage = $e->getMessage();
        }
    }

    /**
     * Polling: consultar estado del enrollment cada 2 segundos
     * Este método se ejecuta automáticamente por wire:poll
     */
    public function pollEnrollmentStatus(): void
    {
        // Solo hacer polling si estamos en estado "enrolling"
        if (!$this->isPolling || $this->enrollmentState !== 'enrolling') {
            return;
        }

        try {
            $response = Http::timeout(5)->get(config('fingerprint.esp32_url') . '/fingerprint/enroll-status');

            if ($response->successful()) {
                $data = $response->json();

                // Actualizar estado local
                $this->enrollmentProgress = $data['progress'] ?? 0;

                // Mapear estados del ESP32 a mensajes en español
                $stateMessages = [
                    'idle' => 'En espera',
                    'waiting_finger_1' => 'Coloque su dedo en el sensor...',
                    'capturing_1' => 'Capturando primera imagen...',
                    'waiting_remove_1' => 'Retire el dedo del sensor',
                    'waiting_finger_2' => 'Coloque el MISMO dedo nuevamente...',
                    'capturing_2' => 'Capturando segunda imagen...',
                    'creating_model' => 'Creando modelo de huella...',
                    'storing' => 'Guardando en sensor...',
                    'success' => '¡Huella registrada exitosamente!',
                    'error' => 'Error en el registro'
                ];

                $esp32State = $data['state'] ?? 'idle';
                $this->enrollmentMessage = $stateMessages[$esp32State] ?? 'Procesando...';

                // Verificar si terminó con éxito
                if ($esp32State === 'success') {
                    $this->handleEnrollmentSuccess();
                }

                // Verificar si hubo error
                if ($esp32State === 'error') {
                    $this->handleEnrollmentError($data['error_message'] ?? 'Error desconocido');
                }

            } else {
                // Error de comunicación, pero no cancelar aún
                logger()->warning('Error al hacer polling del enrollment', [
                    'status' => $response->status(),
                    'empleado_id' => $this->record->id
                ]);
            }

        } catch (\Exception $e) {
            logger()->error('Excepción al hacer polling del enrollment', [
                'error' => $e->getMessage(),
                'empleado_id' => $this->record->id
            ]);
        }
    }

    /**
     * Manejar enrollment exitoso
     */
    private function handleEnrollmentSuccess(): void
    {
        $this->isPolling = false;
        $this->enrollmentState = 'success';
        $this->enrollmentProgress = 100;

        Notification::make()
            ->success()
            ->title('¡Huella registrada!')
            ->body('La huella dactilar ha sido registrada exitosamente. El empleado ya puede marcar asistencia.')
            ->seconds(5)
            ->send();

        // Redirigir después de 3 segundos
        $this->js('setTimeout(() => { window.location.href = "' . EmpleadoResource::getUrl('index') . '" }, 3000)');
    }

    /**
     * Manejar error en enrollment
     */
    private function handleEnrollmentError(string $errorMessage): void
    {
        $this->isPolling = false;
        $this->enrollmentState = 'error';
        $this->enrollmentProgress = 0;

        Notification::make()
            ->danger()
            ->title('Error en el registro')
            ->body($errorMessage)
            ->persistent()
            ->send();

        // Reiniciar estado para permitir reintento
        $this->assignedSlotId = null;
    }

    /**
     * Cancelar el enrollment en progreso
     */
    public function cancelEnrollment(): void
    {
        if ($this->assignedSlotId !== null) {
            try {
                // Intentar eliminar el slot del ESP32
                Http::timeout(5)->delete(
                    config('fingerprint.esp32_url') . '/fingerprint/delete-slot',
                    ['slot' => $this->assignedSlotId]
                );
            } catch (\Exception $e) {
                logger()->warning('No se pudo eliminar slot al cancelar', [
                    'slot' => $this->assignedSlotId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->isPolling = false;
        $this->enrollmentState = 'idle';
        $this->enrollmentProgress = 0;
        $this->enrollmentMessage = 'Registro cancelado';
        $this->assignedSlotId = null;

        Notification::make()
            ->warning()
            ->title('Registro cancelado')
            ->body('El proceso de registro fue cancelado.')
            ->send();
    }

    /**
     * Saltar el registro de huella para hacerlo después
     */
    public function skipEnrollment(): void
    {
        Notification::make()
            ->warning()
            ->title('Registro pospuesto')
            ->body('El empleado quedará en estado "Huella pendiente". Puede registrar la huella más tarde.')
            ->send();

        $this->redirect(EmpleadoResource::getUrl('index'));
    }
}
