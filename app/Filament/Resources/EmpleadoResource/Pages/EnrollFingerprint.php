<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use App\Models\Empleado;
use App\Services\FingerprintService;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

class EnrollFingerprint extends Page
{
    protected static string $resource = EmpleadoResource::class;

    protected string $view = 'filament.resources.empleado-resource.pages.enroll-fingerprint';

    public Empleado $record;

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
        $service = new FingerprintService();

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

        // TODO: Comunicar con ESP32 para iniciar enrollment
        // Por ahora, mostrar el slot asignado
        Notification::make()
            ->info()
            ->title('Slot asignado')
            ->body("Slot #{$availableSlot} disponible para registro")
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
