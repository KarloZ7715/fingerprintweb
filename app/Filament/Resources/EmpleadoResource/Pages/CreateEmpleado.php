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
                                ->required()
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
                                ->required()
                                ->numeric()
                                ->default('57')
                                ->minLength(1)
                                ->maxLength(4)
                                ->prefix('+')
                                ->placeholder('57')
                                ->columnSpan(1),

                            TextInput::make('telefono')
                                ->label('Número de Teléfono')
                                ->required()
                                ->tel()
                                ->placeholder('310 1234567')
                                ->mask(fn($state) => strlen(preg_replace('/\D/', '', $state ?? '')) > 10 ? null : '999 9999999')
                                ->stripCharacters([' ', '-', '(', ')'])
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
     * Validación y preparación de datos antes de crear el empleado
     * Establece estado inicial como Pendiente_Huella para integración con sensor
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // VALIDAR QUE LA HUELLA FUE REGISTRADA (Step 3)
        if ($this->enrollmentState !== 'success') {
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

        // Validar email con @
        if (!str_contains($data['email'], '@')) {
            throw ValidationException::withMessages([
                'email' => 'El correo electrónico debe contener @',
            ]);
        }

        // Validar código de país (solo números)
        if (!ctype_digit($data['codigo_pais'])) {
            throw ValidationException::withMessages([
                'codigo_pais' => 'El código de país debe contener solo números',
            ]);
        }

        // Validar teléfono (solo números después de limpiar)
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
     * Acciones después de crear el empleado
     * Guardar la huella registrada en el Step 3 en la base de datos
     */
    protected function afterCreate(): void
    {
        logger()->info('afterCreate() ejecutado', [
            'assignedSlotId' => $this->assignedSlotId,
            'lastQualityScore' => $this->lastQualityScore,
            'lastRegisteredTipoDedo' => $this->lastRegisteredTipoDedo,
            'lastRegisteredMano' => $this->lastRegisteredMano,
            'enrollmentState' => $this->enrollmentState,
        ]);

        // Guardar la huella SOLO si tenemos todos los datos necesarios
        if ($this->assignedSlotId !== null && $this->lastQualityScore !== null) {
            $this->record->huellas()->create([
                'numero_slot' => $this->assignedSlotId,
                'calidad' => $this->lastQualityScore,
                'tipo_dedo' => $this->lastRegisteredTipoDedo ?? 'Indice',
                'mano' => $this->lastRegisteredMano ?? 'Derecha',
                'estado' => 'Activa',
                'enrolado_por' => auth()->id(),
                'fecha_enrolamiento' => now(),
            ]);

            // Actualizar estado del empleado a Activo (ya tiene huella)
            $this->record->update([
                'estado' => 'Activo',
            ]);

            logger()->info('✓ Huella guardada en BD después de crear empleado', [
                'empleado_id' => $this->record->id,
                'numero_slot' => $this->assignedSlotId,
                'calidad' => $this->lastQualityScore,
            ]);
        } else {
            logger()->warning('✗ No se guardó la huella: condición no cumplida', [
                'assignedSlotId_is_null' => $this->assignedSlotId === null,
                'lastQualityScore_is_null' => $this->lastQualityScore === null,
                'enrollmentState' => $this->enrollmentState,
            ]);
        }
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
        try {
            logger()->info('[ENROLLMENT] Enviando POST a ESP32', [
                'url' => config('fingerprint.esp32_url') . '/fingerprint/start-enroll',
                'slot_id' => $availableSlot,
                'empleado_id' => 0,
            ]);

            $response = Http::timeout(10)->post(config('fingerprint.esp32_url') . '/fingerprint/start-enroll', [
                'empleado_id' => 0, // Temporal, se actualizará después
                'slot_id' => $availableSlot
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    $this->assignedSlotId = $availableSlot;
                    $this->enrollmentState = 'enrolling';
                    $this->enrollmentProgress = 5;
                    $this->enrollmentMessage = 'Iniciando proceso de registro... Preparando sensor';
                    $this->isPolling = true;
                    $this->currentAttempt = 1;

                    Notification::make()
                        ->success()
                        ->title('Registro iniciado')
                        ->body("Slot #{$availableSlot} asignado. Siga las instrucciones.")
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
            $response = Http::timeout(5)->get(config('fingerprint.esp32_url') . '/fingerprint/enroll-status');

            if ($response->successful()) {
                $data = $response->json();

                logger()->debug('[POLLING] Respuesta ESP32', [
                    'state' => $data['state'] ?? 'unknown',
                    'progress' => $data['progress'] ?? 0,
                    'quality_score' => $data['quality_score'] ?? 'not_set',
                ]);

                // Actualizar progreso
                $this->enrollmentProgress = $data['progress'] ?? 0;

                // Mapear estados del ESP32 a mensajes profesionales
                $stateMessages = [
                    'idle' => 'Sistema listo',
                    'waiting_finger_1' => "Coloque su dedo {$this->selectedTipoDedo} de la mano {$this->selectedMano} sobre el sensor",
                    'capturing_1' => 'Capturando primera imagen... Mantenga el dedo inmóvil',
                    'waiting_remove_1' => 'Retire el dedo del sensor y espere la siguiente indicación',
                    'waiting_finger_2' => 'Coloque el MISMO dedo nuevamente en la misma posición',
                    'capturing_2' => 'Capturando segunda imagen... Mantenga el dedo inmóvil',
                    'creating_model' => 'Procesando y creando modelo de la huella dactilar',
                    'storing' => 'Guardando huella en el sensor',
                    'success' => 'Huella dactilar registrada exitosamente',
                    'error' => 'Error en el proceso de registro'
                ];

                $esp32State = $data['state'] ?? 'idle';
                $this->enrollmentMessage = $stateMessages[$esp32State] ?? 'Procesando...';

                // Detectar si pasamos a segundo intento
                if ($esp32State === 'waiting_finger_2' && $this->currentAttempt === 1) {
                    $this->currentAttempt = 2;
                }

                // Verificar si terminó con éxito
                if ($esp32State === 'success') {
                    // Verificar quality score
                    $qualityScore = $data['quality_score'] ?? 0;

                    logger()->info('[POLLING] Estado SUCCESS detectado', [
                        'quality_score_raw' => $data['quality_score'] ?? 'not_in_response',
                        'quality_score' => $qualityScore,
                        'threshold' => 80,
                    ]);

                    if ($qualityScore < 80) {
                        $this->handleEnrollmentError("Score muy bajo ({$qualityScore}/255). Mínimo requerido: 80. Intente de nuevo con el dedo limpio y seco.");

                        // Eliminar del sensor
                        $this->deleteSlotFromSensor($this->assignedSlotId);
                    } else {
                        logger()->info('[POLLING] Llamando a handleEnrollmentSuccess()', [
                            'quality_score' => $qualityScore,
                        ]);
                        $this->handleEnrollmentSuccess($qualityScore);
                    }
                }

                // Verificar si hubo error
                if ($esp32State === 'error') {
                    $this->handleEnrollmentError($data['error_message'] ?? 'Error desconocido');
                }

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
        try {
            // Notificar al ESP32 para cancelar el proceso activo
            $response = Http::timeout(5)->post(config('fingerprint.esp32_url') . '/fingerprint/cancel-enroll');

            if (!$response->successful()) {
                logger()->warning('No se pudo notificar cancelación al ESP32', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            logger()->error('Error al cancelar enrollment en ESP32', [
                'error' => $e->getMessage()
            ]);
        }

        // Eliminar slot del sensor si fue asignado
        if ($this->assignedSlotId !== null) {
            $this->deleteSlotFromSensor($this->assignedSlotId);
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
