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
    public ?int $commandId = null;
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

        // Verificar conexión con ESP32 - OMITIDO EN MODO POLLING
        // $connection = $service->checkEsp32Connection();
        // if (!$connection['connected']) { ... }

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

        // Comunicar con ESP32 para iniciar enrollment (vía Polling)
        try {
            $result = $service->enrollFingerprint(
                empleadoId: $this->record->id,
                slotId: $availableSlot,
                qualityScore: 0, // No relevante al inicio
                adminId: auth()->id()
            );

            if ($result['success']) {
                $this->assignedSlotId = $availableSlot;
                $this->commandId = $result['command_id'];
                $this->enrollmentState = 'enrolling';
                $this->enrollmentProgress = 5;
                $this->enrollmentMessage = 'Comando enviado. Esperando que el dispositivo lo procese...';
                $this->isPolling = true;

                Notification::make()
                    ->success()
                    ->title('Solicitud enviada')
                    ->body("El dispositivo iniciará el registro en breve (Slot #{$availableSlot}).")
                    ->send();
            } else {
                throw new \Exception($result['message'] ?? 'Error desconocido');
            }

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al iniciar registro')
                ->body('No se pudo encolar el comando: ' . $e->getMessage())
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
            $service = app(FingerprintService::class);
            $command = $service->getCommandStatus($this->commandId);

            if (!$command) {
                $this->isPolling = false;
                return;
            }

            // Mapear estados del comando a mensajes
            if ($command->status === 'pending') {
                $this->enrollmentMessage = 'Esperando conexión del dispositivo...';
                $this->enrollmentProgress = 5;
            } elseif ($command->status === 'processing') {
                // Leer progreso detallado del enrollment
                $result = $command->result;
                
                if (isset($result['enrollment_state'])) {
                    // Hay información de progreso detallada
                    $this->enrollmentProgress = $result['progress'] ?? 50;
                    $this->enrollmentMessage = $result['message'] ?? 'Procesando...';
                } else {
                    // Fallback si no hay información detallada
                    $this->enrollmentMessage = 'Dispositivo procesando... Siga las instrucciones.';
                    $this->enrollmentProgress = 50;
                }
            } elseif ($command->status === 'completed') {
                $result = $command->result;
                // Verificar quality score si está disponible
                if (isset($result['quality_score'])) {
                    // Éxito
                    $this->handleEnrollmentSuccess();
                } else {
                    // Completado pero sin score? Asumimos éxito
                    $this->handleEnrollmentSuccess();
                }
            } elseif ($command->status === 'failed') {
                $result = $command->result;
                $errorMsg = $result['error'] ?? 'Error desconocido';
                $this->handleEnrollmentError($errorMsg);
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

        // Detectar si es un error de huella duplicada
        $isDuplicate = stripos($errorMessage, 'duplicada') !== false || 
                       stripos($errorMessage, 'duplicate') !== false ||
                       stripos($errorMessage, 'ya existe') !== false;

        if ($isDuplicate) {
            // Extraer el número de slot si está presente
            preg_match('/slot\s*:?\s*(\d+)/i', $errorMessage, $matches);
            $slotNumber = $matches[1] ?? 'desconocido';

            Notification::make()
                ->warning()
                ->title('⚠️ Huella Duplicada Detectada')
                ->body("Esta huella ya está registrada en el sensor (Slot #{$slotNumber}). " .
                       "Cada empleado debe usar una huella única. " .
                       "Por favor, use un dedo diferente o verifique si esta huella pertenece a otro empleado.")
                ->persistent()
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('retry')
                        ->label('Intentar con otro dedo')
                        ->button()
                        ->color('primary')
                        ->close(),
                    \Filament\Notifications\Actions\Action::make('view_slots')
                        ->label('Ver huellas registradas')
                        ->button()
                        ->color('gray')
                        ->url(route('filament.admin.resources.empleados.index'))
                        ->close(),
                ])
                ->send();
        } else {
            // Error genérico
            Notification::make()
                ->danger()
                ->title('Error en el registro')
                ->body($errorMessage)
                ->persistent()
                ->send();
        }

        // Reiniciar estado para permitir reintento
        $this->assignedSlotId = null;
    }

    /**
     * Cancelar el enrollment en progreso
     */
    public function cancelEnrollment(): void
    {
        if ($this->commandId !== null) {
            try {
                $service = app(FingerprintService::class);
                $service->cancelCommand($this->commandId);
            } catch (\Exception $e) {
                logger()->warning('No se pudo cancelar comando', [
                    'command_id' => $this->commandId,
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
