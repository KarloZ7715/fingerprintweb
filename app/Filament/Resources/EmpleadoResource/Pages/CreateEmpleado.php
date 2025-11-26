<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use App\Models\Huella;
use App\Services\FingerprintService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CreateEmpleado extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = EmpleadoResource::class;

    // Estado del enrollment (manejado por LiveWire)
    public string $enrollmentState = 'idle'; // idle, enrolling, success, error
    public int $enrollmentProgress = 0; // 0-100
    public string $enrollmentMessage = 'Listo para iniciar registro';
    public ?int $assignedSlotId = null;
    public ?int $commandId = null;
    public bool $isPolling = false;
    public string $selectedTipoDedo = 'Índice';
    public string $selectedMano = 'Derecha';
    public int $currentAttempt = 1;
    public int $maxAttempts = 2; // Captura 2 veces la misma huella

    // Variables para detectar cambios de dedo/mano (DEBEN SER PÚBLICAS para persistir en Livewire)
    public ?string $lastRegisteredTipoDedo = null;
    public ?string $lastRegisteredMano = null;
    public ?int $lastQualityScore = null;

    /**
     * Configurar pasos del wizard
     */
    protected function getSteps(): array
    {
        return [
            Step::make('Información Personal')
                ->description('Datos personales del empleado')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('cedula')
                                ->label('Cédula')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->numeric()
                                ->minLength(6)
                                ->maxLength(15)
                                ->placeholder('1234567890')
                                ->helperText('Debe ser única en el sistema'),

                            TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email()
                                ->maxLength(100)
                                ->placeholder('ejemplo@correo.com')
                                ->helperText('Formato: usuario@dominio.com'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Primer nombre')
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),

                            TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre (Opcional)')
                                ->maxLength(50)
                                ->placeholder('Segundo nombre')
                                ->rules(['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Primer apellido')
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),

                            TextInput::make('segundo_apellido')
                                ->label('Segundo Apellido')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Segundo apellido')
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),
                        ]),

                    Grid::make(3)
                        ->schema([
                            TextInput::make('codigo_pais')
                                ->label('Código País')
                                ->numeric()
                                ->default('57')
                                ->minLength(1)
                                ->maxLength(4)
                                ->prefix('+')
                                ->placeholder('57')
                                ->columnSpan(1),

                            TextInput::make('telefono')
                                ->label('Número de Teléfono')
                                ->tel()
                                ->placeholder('310 1234567')
                                ->formatStateUsing(function ($state) {
                                    if (!$state)
                                        return $state;
                                    $cleaned = preg_replace('/\D/', '', $state);
                                    if (strlen($cleaned) === 10) {
                                        return substr($cleaned, 0, 3) . ' ' . substr($cleaned, 3);
                                    }
                                    return $cleaned;
                                })
                                ->dehydrateStateUsing(fn($state) => preg_replace('/\D/', '', $state))
                                ->minLength(7)
                                ->maxLength(15)
                                ->columnSpan(2),
                        ]),
                ])
                ->icon('heroicon-o-user'),

            Step::make('Información Laboral')
                ->description('Asignación de sucursal, horario y estado')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('sucursal_id')
                                ->label('Sucursal')
                                ->required()
                                ->relationship('sucursal', 'nombre')
                                ->searchable()
                                ->preload()
                                ->helperText('Sucursal donde labora el empleado'),

                            Select::make('horario_id')
                                ->label('Horario')
                                ->relationship('horario', 'nombre')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Asignar si tiene horario fijo'),
                        ]),

                    FileUpload::make('foto_url')
                        ->label('Foto (Opcional)')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->directory('empleados/fotos')
                        ->visibility('public')
                        ->nullable()
                        ->helperText('Foto de perfil del empleado (máx. 2MB)'),
                ])
                ->icon('heroicon-o-briefcase'),

            Step::make('Registro de Huella')
                ->description('Captura de huella dactilar con sensor AS608')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('tipo_dedo')
                                ->label('Tipo de Dedo')
                                ->options([
                                    'Pulgar' => 'Pulgar',
                                    'Índice' => 'Índice',
                                    'Medio' => 'Medio',
                                    'Anular' => 'Anular',
                                    'Meñique' => 'Meñique',
                                ])
                                ->default('Índice')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->handleFingerOrHandChange('tipo_dedo', $state);
                                })
                                ->disabled(fn() => $this->enrollmentState === 'enrolling' || $this->enrollmentState === 'success')
                                ->helperText('Seleccione el dedo que registrará'),

                            Select::make('mano')
                                ->label('Mano')
                                ->options([
                                    'Derecha' => 'Derecha',
                                    'Izquierda' => 'Izquierda',
                                ])
                                ->default('Derecha')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->handleFingerOrHandChange('mano', $state);
                                })
                                ->disabled(fn() => $this->enrollmentState === 'enrolling' || $this->enrollmentState === 'success')
                                ->helperText('Seleccione la mano'),
                        ]),

                    // Componente visual personalizado para el registro
                    ViewField::make('fingerprint_enrollment')
                        ->label('')
                        ->view('filament.forms.components.fingerprint-enrollment-widget')
                        ->viewData([
                            'enrollmentState' => &$this->enrollmentState,
                            'enrollmentProgress' => &$this->enrollmentProgress,
                            'enrollmentMessage' => &$this->enrollmentMessage,
                            'currentAttempt' => &$this->currentAttempt,
                            'maxAttempts' => &$this->maxAttempts,
                        ]),
                ])
                ->icon('heroicon-o-finger-print'),
        ];
    }

    /**
     * Redirección deshabilitada, se maneja en afterCreate()
     */
    protected function getRedirectUrl(): string
    {
        // Se sobrescribe en afterCreate con redirect manual
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Empleado registrado exitosamente';
    }

    /**
     * Sobrescribir para especificar el método Livewire que el wizard llamará
     */
    protected function getSubmitFormLivewireMethodName(): string
    {
        return 'create';
    }

    public function create(bool $another = false): void
    {
        logger()->info('[create] Método create() llamado', [
            'another' => $another,
            'enrollmentState' => $this->enrollmentState,
            'assignedSlotId' => $this->assignedSlotId,
        ]);

        try {
            logger()->info('[create] ANTES de llamar parent::create()');
            // Llamar al método padre que maneja todo el proceso de creación
            parent::create($another);
            logger()->info('[create] DESPUÉS de llamar parent::create()');
        } catch (\Exception $e) {
            logger()->error('[create] EXCEPCIÓN capturada', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Validación y preparación de datos antes de crear el empleado
     * Establece estado inicial como Pendiente_Huella para integración con sensor
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        logger()->info('[mutateFormDataBeforeCreate] INICIO', [
            'enrollmentState' => $this->enrollmentState,
            'assignedSlotId' => $this->assignedSlotId,
            'lastQualityScore' => $this->lastQualityScore,
        ]);

        // VALIDAR QUE LA HUELLA FUE REGISTRADA (Step 3)
        if ($this->enrollmentState !== 'success') {
            logger()->warning('[mutateFormDataBeforeCreate] Validación falló: huella no registrada', [
                'enrollmentState' => $this->enrollmentState,
            ]);

            Notification::make()
                ->warning()
                ->title('Huella no registrada')
                ->body('Debe completar el registro de la huella dactilar antes de crear el empleado.')
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'fingerprint_enrollment' => 'Debe completar el registro de la huella dactilar.',
            ]);
        }

        logger()->info('[mutateFormDataBeforeCreate] Validación de huella pasó correctamente');

        // Validar email con @ si existe
        if (!empty($data['email']) && !str_contains($data['email'], '@')) {
            throw ValidationException::withMessages([
                'email' => 'El correo electrónico debe contener @',
            ]);
        }

        // Limpiar y validar código de país (solo números) si existe
        if (!empty($data['codigo_pais'])) {
            $data['codigo_pais'] = preg_replace('/[^0-9]/', '', $data['codigo_pais']);
            if (!ctype_digit($data['codigo_pais'])) {
                throw ValidationException::withMessages([
                    'codigo_pais' => 'El código de país debe contener solo números',
                ]);
            }
        }

        // Limpiar y validar teléfono (solo números después de limpiar) si existe
        if (!empty($data['telefono'])) {
            $data['telefono'] = preg_replace('/[^0-9]/', '', $data['telefono']);
            if (!ctype_digit($data['telefono'])) {
                throw ValidationException::withMessages([
                    'telefono' => 'El teléfono debe contener solo números',
                ]);
            }

            // Validar longitud de teléfono
            $length = strlen($data['telefono']);
            if ($length < 7 || $length > 15) {
                throw ValidationException::withMessages([
                    'telefono' => 'El teléfono debe tener entre 7 y 15 dígitos',
                ]);
            }
        }

        // Convertir campos opcionales vacíos a null explícitamente
        $data['segundo_nombre'] = $data['segundo_nombre'] ?? null;
        $data['horario_id'] = $data['horario_id'] ?? null;
        $data['foto_url'] = $data['foto_url'] ?? null;

        // IMPORTANTE: Establecer estado inicial como Pendiente_Huella
        // El empleado debe registrar su huella antes de estar Activo
        $data['estado'] = 'Pendiente_Huella';

        return $data;
    }

    /**
     * Sobrescribir método del trait HasWizard
     * Se ejecuta para crear el registro en la base de datos
     * CRÍTICO: Este método SÍ se ejecuta con HasWizard (a diferencia de afterCreate)
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        logger()->info('[handleRecordCreation] INICIO', [
            'assignedSlotId' => $this->assignedSlotId,
            'lastQualityScore' => $this->lastQualityScore,
            'enrollmentState' => $this->enrollmentState,
        ]);

        // Crear el empleado con estado Pendiente_Huella
        $empleado = static::getModel()::create($data);

        logger()->info('[handleRecordCreation] Empleado creado', [
            'empleado_id' => $empleado->id,
            'estado' => $empleado->estado,
        ]);

        // Guardar la huella SOLO si tenemos todos los datos necesarios
        if ($this->assignedSlotId !== null && $this->lastQualityScore !== null) {
            $huella = $empleado->huellas()->create([
                'numero_slot' => $this->assignedSlotId,
                'calidad' => $this->lastQualityScore,
                'tipo_dedo' => $this->lastRegisteredTipoDedo ?? 'Indice',
                'mano' => $this->lastRegisteredMano ?? 'Derecha',
                'estado' => 'Activa',
                'enrolado_por' => auth()->id(),
                'fecha_enrolamiento' => now(),
            ]);

            // Actualizar estado del empleado a Activo (ya tiene huella)
            $empleado->update(['estado' => 'Activo']);

            logger()->info('[handleRecordCreation] Huella guardada correctamente', [
                'empleado_id' => $empleado->id,
                'huella_id' => $huella->id,
                'numero_slot' => $this->assignedSlotId,
                'calidad' => $this->lastQualityScore,
            ]);
        } else {
            logger()->warning('[handleRecordCreation] No se guardó la huella', [
                'assignedSlotId_is_null' => $this->assignedSlotId === null,
                'lastQualityScore_is_null' => $this->lastQualityScore === null,
            ]);
        }

        return $empleado;
    }

    /**
     * Manejar cambio de dedo o mano durante/después del enrollment
     * Si se cambia, se debe resetear y registrar nuevamente
     */
    public function handleFingerOrHandChange(string $field, string $newValue): void
    {
        // Actualizar la variable correspondiente
        if ($field === 'tipo_dedo') {
            $this->selectedTipoDedo = $newValue;
        } else {
            $this->selectedMano = $newValue;
        }

        // Si hay un enrollment activo o completado, resetear
        if ($this->enrollmentState === 'enrolling' || $this->enrollmentState === 'success') {
            // Detectar si hubo cambio real
            $tipoChanged = ($field === 'tipo_dedo' && $this->lastRegisteredTipoDedo !== null && $this->lastRegisteredTipoDedo !== $newValue);
            $manoChanged = ($field === 'mano' && $this->lastRegisteredMano !== null && $this->lastRegisteredMano !== $newValue);

            if ($tipoChanged || $manoChanged) {
                // Si hay un slot asignado, eliminarlo del sensor
                if ($this->assignedSlotId !== null) {
                    $this->deleteSlotFromSensor($this->assignedSlotId);

                    Notification::make()
                        ->warning()
                        ->title('Registro cancelado')
                        ->body("Cambió el dedo/mano. Debe registrar nuevamente: {$this->selectedTipoDedo} ({$this->selectedMano})")
                        ->send();
                }

                // Resetear estado
                $this->enrollmentState = 'idle';
                $this->enrollmentProgress = 0;
                $this->enrollmentMessage = "Listo para registrar {$this->selectedTipoDedo} ({$this->selectedMano})";
                $this->assignedSlotId = null;
                $this->isPolling = false;
                $this->currentAttempt = 1;
                $this->lastRegisteredTipoDedo = null;
                $this->lastRegisteredMano = null;
            }
        }
    }

    /**
     * Iniciar enrollment desde el wizard
     */
    public function startEnrollment(): void
    {
        $service = app(FingerprintService::class);

        // Verificar conexión con ESP32 - OMITIDO EN MODO POLLING
        // $connection = $service->checkEsp32Connection();
        // if (!$connection['connected']) { ... }

        // Obtener slot disponible
        $availableSlot = $service->getAvailableSlot();

        logger()->info('[ENROLLMENT] Slot asignado por getAvailableSlot()', [
            'slot_id' => $availableSlot,
            'is_null' => $availableSlot === null,
        ]);

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
        // Comunicar con ESP32 para iniciar enrollment (vía Polling)
        try {
            logger()->info('[ENROLLMENT] Encolando comando de enrollment', [
                'slot_id' => $availableSlot,
                'empleado_id' => 0,
            ]);

            $result = $service->enrollFingerprint(
                empleadoId: 0, // Temporal, se actualizará después
                slotId: $availableSlot,
                qualityScore: 0, // No relevante al inicio
                adminId: auth()->id(),
                tipoDedo: $this->selectedTipoDedo,
                mano: $this->selectedMano
            );

            if ($result['success']) {
                $this->assignedSlotId = $availableSlot;
                $this->commandId = $result['command_id'];
                $this->enrollmentState = 'enrolling';
                $this->enrollmentProgress = 5;
                $this->enrollmentMessage = 'Comando enviado. Esperando que el dispositivo lo procese...';
                $this->isPolling = true;
                $this->currentAttempt = 1;

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
     */
    public function pollEnrollmentStatus(): void
    {
        // Solo hacer polling si estamos en estado "enrolling"
        if (!$this->isPolling || $this->enrollmentState !== 'enrolling') {
            logger()->debug('[POLLING] Polling detenido', [
                'isPolling' => $this->isPolling,
                'enrollmentState' => $this->enrollmentState,
            ]);
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
                    
                    // Actualizar intento si estamos en el segundo paso
                    if ($result['enrollment_state'] === 'waiting_finger_2' && $this->currentAttempt === 1) {
                        $this->currentAttempt = 2;
                    }
                } else {
                    // Fallback si no hay información detallada
                    $this->enrollmentMessage = 'Dispositivo procesando... Siga las instrucciones en pantalla/audio.';
                    $this->enrollmentProgress = 50;
                }
            } elseif ($command->status === 'completed') {
                $result = $command->result;
                $qualityScore = $result['quality_score'] ?? 0;
                
                // Verificar quality score
                if ($qualityScore < 80) {
                    $this->handleEnrollmentError("Score muy bajo ({$qualityScore}/255). Mínimo requerido: 80. Intente de nuevo con el dedo limpio y seco.");
                    // Eliminar slot (opcional, ya que no se guardó en BD)
                } else {
                    $this->handleEnrollmentSuccess($qualityScore);
                }
            } elseif ($command->status === 'failed') {
                $result = $command->result;
                $errorMsg = $result['error'] ?? 'Error desconocido';
                $this->handleEnrollmentError($errorMsg);
            }

        } catch (\Exception $e) {
            logger()->error('Excepción al hacer polling del enrollment', [
                'error' => $e->getMessage(),
            ]);
        }


    }

    /**
     * Manejar enrollment exitoso
     */
    private function handleEnrollmentSuccess(int $qualityScore): void
    {
        logger()->info('[handleEnrollmentSuccess] INICIO', [
            'quality_score_param' => $qualityScore,
            'current_lastQualityScore' => $this->lastQualityScore,
        ]);

        $this->isPolling = false;
        $this->enrollmentState = 'success';
        $this->enrollmentProgress = 100;

        // Guardar qué dedo/mano fue registrado y quality score
        $this->lastRegisteredTipoDedo = $this->selectedTipoDedo;
        $this->lastRegisteredMano = $this->selectedMano;
        $this->lastQualityScore = $qualityScore;

        logger()->info('[handleEnrollmentSuccess] Variables asignadas', [
            'lastRegisteredTipoDedo' => $this->lastRegisteredTipoDedo,
            'lastRegisteredMano' => $this->lastRegisteredMano,
            'lastQualityScore' => $this->lastQualityScore,
            'assignedSlotId' => $this->assignedSlotId,
        ]);

        Notification::make()
            ->success()
            ->title('¡Huella registrada!')
            ->body("Calidad: {$qualityScore}/255. Dedo: {$this->selectedTipoDedo} ({$this->selectedMano}).")
            ->seconds(5)
            ->send();
    }

    /**
     * Manejar error en enrollment
     */
    private function handleEnrollmentError(string $errorMessage): void
    {
        $this->isPolling = false;
        $this->enrollmentState = 'error';
        $this->enrollmentProgress = 0;
        $this->currentAttempt = 1;

        // Resetear registros de dedo/mano al haber error
        $this->lastRegisteredTipoDedo = null;
        $this->lastRegisteredMano = null;

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
        // Cancelar comando si existe
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

        // Resetear estado en el frontend
        $this->isPolling = false;
        $this->enrollmentState = 'idle';
        $this->enrollmentProgress = 0;
        $this->enrollmentMessage = 'Registro cancelado. Puede intentar nuevamente.';
        $this->assignedSlotId = null;
        $this->currentAttempt = 1;
        $this->lastRegisteredTipoDedo = null;
        $this->lastRegisteredMano = null;

        Notification::make()
            ->warning()
            ->title('Registro cancelado')
            ->body('El proceso de registro fue cancelado correctamente.')
            ->send();
    }

    /**
     * Eliminar slot del sensor
     */
    private function deleteSlotFromSensor(int $slotId): void
    {
        try {
            Http::timeout(5)->delete(
                config('fingerprint.esp32_url') . '/fingerprint/delete-slot',
                ['slot' => $slotId]
            );
        } catch (\Exception $e) {
            logger()->warning('No se pudo eliminar slot al cancelar', [
                'slot' => $slotId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
