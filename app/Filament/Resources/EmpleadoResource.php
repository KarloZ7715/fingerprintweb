<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Models\Empleado;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Empleados';
    protected static ?string $pluralModelLabel = 'Empleados';
    protected static ?string $modelLabel = 'Empleado';
    protected static UnitEnum|string|null $navigationGroup = 'Gestión de Personal';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información Personal')
                    ->description('Datos personales del empleado')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('cedula')
                                    ->label('Cédula')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->placeholder('Ingrese la cédula')
                                    ->helperText('Debe ser única en el sistema'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('ejemplo@correo.com')
                                    ->helperText('Formato: usuario@dominio.com'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('primer_nombre')
                                    ->label('Primer Nombre')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Primer nombre')
                                    ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),

                                Forms\Components\TextInput::make('segundo_nombre')
                                    ->label('Segundo Nombre (Opcional)')
                                    ->maxLength(50)
                                    ->placeholder('Segundo nombre')
                                    ->rules(['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('primer_apellido')
                                    ->label('Primer Apellido')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Primer apellido')
                                    ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),

                                Forms\Components\TextInput::make('segundo_apellido')
                                    ->label('Segundo Apellido')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Segundo apellido')
                                    ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/']),
                            ]),

                        Forms\Components\TextInput::make('telefono')
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
                    ->collapsible(),

                Section::make('Información Laboral')
                    ->description('Asignación de sucursal, horario y estado laboral')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('sucursal_id')
                                    ->label('Sucursal')
                                    ->required()
                                    ->relationship('sucursal', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Sucursal donde labora el empleado'),

                                Forms\Components\Select::make('horario_id')
                                    ->label('Horario')
                                    ->relationship('horario', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Asignar si tiene horario fijo'),

                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->required()
                                    ->options([
                                        'Activo' => 'Activo',
                                        'Inactivo' => 'Inactivo',
                                        'Suspendido' => 'Suspendido',
                                        'Vacaciones' => 'Vacaciones',
                                    ])
                                    ->default('Activo')
                                    ->helperText('Estado actual del empleado'),
                            ]),

                        Forms\Components\FileUpload::make('foto_url')
                            ->label('Foto (Opcional)')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->directory('empleados/fotos')
                            ->visibility('public')
                            ->nullable()
                            ->helperText('Foto de perfil del empleado (máx. 2MB)'),
                    ])
                    ->collapsible(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')
                    ->label('Cédula')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->getStateUsing(fn(Empleado $record) => $record->nombre_completo)
                    ->searchable(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(function ($state) {
                        if (empty($state))
                            return $state;

                        // Detectar código de país y formatear inteligentemente
                        $length = strlen($state);

                        // Colombia (57): +57 XXX XXXXXXX
                        if (str_starts_with($state, '57') && $length == 12) {
                            return '+' . substr($state, 0, 2) . ' ' . substr($state, 2, 3) . ' ' . substr($state, 5);
                        }

                        // USA/Canadá (1): +1 XXX XXX XXXX
                        if (str_starts_with($state, '1') && $length == 11) {
                            return '+' . substr($state, 0, 1) . ' ' . substr($state, 1, 3) . ' ' . substr($state, 4, 3) . ' ' . substr($state, 7);
                        }

                        // España (34), Francia (33), Italia (39): +XX XXX XXX XXX
                        if (($length >= 11 && $length <= 12) && preg_match('/^(34|33|39)/', $state)) {
                            return '+' . substr($state, 0, 2) . ' ' . substr($state, 2, 3) . ' ' . substr($state, 5, 3) . ' ' . substr($state, 8);
                        }

                        // México (52): +52 XXX XXX XXXX
                        if (str_starts_with($state, '52') && $length == 12) {
                            return '+' . substr($state, 0, 2) . ' ' . substr($state, 2, 3) . ' ' . substr($state, 5, 3) . ' ' . substr($state, 8);
                        }

                        // Formato genérico para otros países: +XX XXXXXXXXXX
                        return '+' . substr($state, 0, 2) . ' ' . substr($state, 2);
                    })
                    ->copyable()
                    ->copyableState(fn($state) => $state), // Copia sin espacios, solo números

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Activo' => 'success',
                        'Inactivo' => 'danger',
                        'Suspendido' => 'warning',
                        'Vacaciones' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->timezone('America/Bogota'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                        'Suspendido' => 'Suspendido',
                        'Vacaciones' => 'Vacaciones',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->relationship('sucursal', 'nombre')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Registrado desde')
                            ->timezone('America/Bogota')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Registrado hasta')
                            ->timezone('America/Bogota')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
                Action::make('cambiar_estado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('estado')
                            ->label('Nuevo Estado')
                            ->options([
                                'Activo' => 'Activo',
                                'Inactivo' => 'Inactivo',
                                'Suspendido' => 'Suspendido',
                                'Vacaciones' => 'Vacaciones',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Empleado $record, array $data): void {
                        $record->update(['estado' => $data['estado']]);
                    })
                    ->successNotificationTitle('Estado actualizado correctamente')
                    ->color('warning'),
            ])
            ->bulkActions([
                BulkAction::make('cambiar_estado_masivo')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('estado')
                            ->label('Nuevo Estado')
                            ->options([
                                'Activo' => 'Activo',
                                'Inactivo' => 'Inactivo',
                                'Suspendido' => 'Suspendido',
                                'Vacaciones' => 'Vacaciones',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(function ($records, array $data): void {
                        $records->each->update(['estado' => $data['estado']]);
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Estados actualizados correctamente')
                    ->color('warning'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}
