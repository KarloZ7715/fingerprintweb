<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HorarioResource\Pages;
use App\Models\Horario;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class HorarioResource extends Resource
{
    protected static ?string $model = Horario::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Horarios';
    protected static ?string $pluralModelLabel = 'Horarios';
    protected static ?string $modelLabel = 'Horario';
    protected static UnitEnum|string|null $navigationGroup = 'Gestión de Personal';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información del Horario')
                    ->description('Configuración básica del turno de trabajo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Horario')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ej: Turno Mañana, Turno Tarde, Administrativo')
                                    ->helperText('Nombre descriptivo del horario')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->placeholder('Descripción detallada del horario...')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Horario de Trabajo')
                    ->description('Define las horas de entrada y salida')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('hora_entrada')
                                    ->label('Hora de Entrada')
                                    ->required()
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('h:i A')
                                    ->helperText('Hora esperada de entrada'),

                                Forms\Components\TimePicker::make('hora_salida')
                                    ->label('Hora de Salida')
                                    ->required()
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('h:i A')
                                    ->helperText('Hora esperada de salida'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Tolerancias')
                    ->description('Margen permitido para entrada y salida (en minutos)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('tolerancia_entrada')
                                    ->label('Tolerancia de Entrada (minutos)')
                                    ->required()
                                    ->numeric()
                                    ->default(15)
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->suffix('min')
                                    ->helperText('Minutos permitidos de retraso'),

                                Forms\Components\TextInput::make('tolerancia_salida')
                                    ->label('Tolerancia de Salida (minutos)')
                                    ->required()
                                    ->numeric()
                                    ->default(15)
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->suffix('min')
                                    ->helperText('Minutos permitidos de salida temprana'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Días Laborables')
                    ->description('Selecciona los días en que aplica este horario')
                    ->schema([
                        Forms\Components\CheckboxList::make('dias_laborables')
                            ->label('')
                            ->options([
                                'lunes' => 'Lunes',
                                'martes' => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves' => 'Jueves',
                                'viernes' => 'Viernes',
                                'sabado' => 'Sábado',
                                'domingo' => 'Domingo',
                            ])
                            ->columns(4)
                            ->gridDirection('row')
                            ->default(['lunes', 'martes', 'miercoles', 'jueves', 'viernes'])
                            ->helperText('Marca los días en que se aplica este horario'),
                    ])
                    ->collapsible(),

                Section::make('Configuración Adicional')
                    ->description('Opciones avanzadas del horario')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('requiere_entrada')
                                    ->label('Requiere Registro de Entrada')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Si está activo, el empleado debe marcar entrada'),

                                Forms\Components\Toggle::make('requiere_salida')
                                    ->label('Requiere Registro de Salida')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Si está activo, el empleado debe marcar salida'),

                                Forms\Components\Toggle::make('activo')
                                    ->label('Horario Activo')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Solo los horarios activos pueden asignarse'),
                            ]),

                        Forms\Components\Select::make('sucursal_id')
                            ->label('Sucursal (Opcional)')
                            ->relationship('sucursal', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Deja vacío si el horario aplica para todas las sucursales')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('hora_entrada')
                    ->label('Entrada')
                    ->time('h:i A')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('hora_salida')
                    ->label('Salida')
                    ->time('h:i A')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('tolerancia_entrada')
                    ->label('Tol. Entrada')
                    ->sortable()
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tolerancia_salida')
                    ->label('Tol. Salida')
                    ->sortable()
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dias_laborables')
                    ->label('Días Laborables')
                    ->getStateUsing(function ($record) {
                        // Obtener directamente del record
                        $dias = $record->dias_laborables;
                        
                        // Si es string, decodificar
                        if (is_string($dias)) {
                            $dias = json_decode($dias, true);
                        }
                        
                        // Validar que sea array
                        if (!is_array($dias) || empty($dias)) {
                            return 'No configurado';
                        }
                        
                        // Filtrar días activos (valor true)
                        $diasActivos = array_keys(array_filter($dias, function($val) {
                            return $val === true || $val === 1 || $val === '1';
                        }));
                        
                        if (empty($diasActivos)) {
                            return 'Ninguno';
                        }
                        
                        $diasAbrev = [
                            'lunes' => 'Lun',
                            'martes' => 'Mar',
                            'miercoles' => 'Mié',
                            'jueves' => 'Jue',
                            'viernes' => 'Vie',
                            'sabado' => 'Sáb',
                            'domingo' => 'Dom',
                        ];
                        
                        return collect($diasActivos)
                            ->map(fn($dia) => $diasAbrev[$dia] ?? ucfirst($dia))
                            ->join(', ');
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('empleados_count')
                    ->label('Empleados Asignados')
                    ->counts('empleados')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->default('Todas')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\ToggleColumn::make('activo')
                    ->label('Activo')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        1 => 'Activo',
                        0 => 'Inactivo',
                    ]),

                Tables\Filters\SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->relationship('sucursal', 'nombre')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('requiere_entrada')
                    ->label('Requiere Entrada')
                    ->query(fn($query) => $query->where('requiere_entrada', true))
                    ->toggle(),

                Tables\Filters\Filter::make('requiere_salida')
                    ->label('Requiere Salida')
                    ->query(fn($query) => $query->where('requiere_salida', true))
                    ->toggle(),
            ])
            ->defaultSort('nombre', 'asc')
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                BulkAction::make('activar')
                    ->label('Activar Seleccionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update(['activo' => true]);
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Horarios activados correctamente'),

                BulkAction::make('desactivar')
                    ->label('Desactivar Seleccionados')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update(['activo' => false]);
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Horarios desactivados correctamente'),
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
            'index' => Pages\ListHorarios::route('/'),
            'create' => Pages\CreateHorario::route('/create'),
            'edit' => Pages\EditHorario::route('/{record}/edit'),
        ];
    }

    /**
     * Estadísticas para el dashboard
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('activo', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
