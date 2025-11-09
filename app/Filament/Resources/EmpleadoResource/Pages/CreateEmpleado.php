<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateEmpleado extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = EmpleadoResource::class;

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
                                ->maxLength(20)
                                ->placeholder('Ingrese la cédula')
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

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->required()
                        ->tel()
                        ->prefix('+')
                        ->placeholder('57 300 1234567')
                        ->stripCharacters([' ', '-', '(', ')'])
                        ->minLength(7)
                        ->maxLength(15)
                        ->helperText('Incluir código de país sin +. Ej: 57 300 1234567 (Colombia)'),
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
                    Section::make('Instrucciones')
                        ->description('El sistema registrará automáticamente la huella del empleado una vez creado el registro básico.')
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('instrucciones_huella')
                                        ->label('')
                                        ->content('
                                            **Pasos para registrar la huella:**
                                            
                                            1. Complete los datos personales y laborales
                                            2. Haga clic en "Crear Empleado"
                                            3. El empleado quedará en estado "Pendiente_Huella"
                                            4. Será redirigido automáticamente a la pantalla de registro de huella
                                            5. Siga las instrucciones en pantalla para capturar la huella
                                            
                                            **Nota:** Si desea registrar la huella más tarde, puede hacerlo desde la lista de empleados.
                                        '),
                                ]),
                        ]),
                ])
                ->icon('heroicon-o-finger-print'),
        ];
    }

    protected function getRedirectUrl(): string
    {
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

        // IMPORTANTE: Establecer estado inicial como Pendiente_Huella
        // El empleado debe registrar su huella antes de estar Activo
        $data['estado'] = 'Pendiente_Huella';

        return $data;
    }

    /**
     * Acciones después de crear el empleado
     * Redirige a la pantalla de registro de huella (futuro)
     */
    protected function afterCreate(): void
    {
        // TODO Fase 3.2: Redirigir a componente LiveWire de enrollment
        // $this->redirect(route('filament.resources.empleados.enroll', ['record' => $this->record]));
    }
}
