<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaResource\Pages;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\Horario;
use App\Models\Sucursal;
use App\Services\AsistenciaService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Asistencias';

    protected static UnitEnum|string|null $navigationGroup = 'Gestión de Personal';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Asistencia';

    protected static ?string $pluralModelLabel = 'Asistencias';

    /**
     * Tabla de asistencias
     * Las asistencias se registran automáticamente cuando el empleado coloca su huella
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('empleado.nombre_completo')
                    ->label('Empleado')
                    ->searchable(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'cedula'])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->join('empleado', 'asistencia.empleado_id', '=', 'empleado.id')
                            ->orderBy('empleado.primer_nombre', $direction)
                            ->orderBy('empleado.primer_apellido', $direction)
                            ->select('asistencia.*');
                    }),

                Tables\Columns\TextColumn::make('empleado.cedula')
                    ->label('Cédula')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('empleado.sucursal.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('hora_registro')
                    ->label('Hora Entrada')
                    ->dateTime('H:i:s'),

                Tables\Columns\TextColumn::make('hora_salida')
                    ->label('Hora Salida')
                    ->dateTime('H:i:s')
                    ->placeholder('Sin registro'),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'Puntual',
                        'warning' => 'Tarde',
                        'danger' => 'Ausente',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Puntual',
                        'heroicon-o-clock' => 'Tarde',
                        'heroicon-o-x-circle' => 'Ausente',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('minutos_diferencia')
                    ->label('Retraso')
                    ->formatStateUsing(fn ($state) => abs($state) > 0 ? abs($state) . " min" : '-')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('metodo_registro')
                    ->label('Método')
                    ->badge()
                    ->colors([
                        'success' => 'Huella',
                        'info' => 'Manual',
                        'warning' => 'Emergencia',
                    ]),

                Tables\Columns\IconColumn::make('justificada')
                    ->label('Justificado')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('motivo_justificacion')
                    ->label('Justificación')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Sin justificación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Puntual' => 'Puntual',
                        'Tarde' => 'Tarde',
                        'Ausente' => 'Ausente',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('empleado_id')
                    ->label('Empleado')
                    ->relationship('empleado', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_completo)
                    ->searchable(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'cedula'])
                    ->preload(),

                Tables\Filters\SelectFilter::make('sucursal')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id'))
                    ->query(function (Builder $query, $state) {
                        if ($state['value'] ?? null) {
                            $query->whereHas('empleado', function ($query) use ($state) {
                                $query->where('sucursal_id', $state['value']);
                            });
                        }
                    }),

                Tables\Filters\Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('justificado')
                    ->label('Justificación')
                    ->placeholder('Todos')
                    ->trueLabel('Justificadas')
                    ->falseLabel('Sin justificar'),

                Tables\Filters\SelectFilter::make('metodo_registro')
                    ->label('Método de Registro')
                    ->options([
                        'Huella' => 'Huella',
                        'Manual' => 'Manual',
                        'Reconocimiento Facial' => 'Reconocimiento Facial',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('hoy')
                    ->label('Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('fecha', Carbon::today()))
                    ->toggle(),

                Tables\Filters\Filter::make('semana_actual')
                    ->label('Esta Semana')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('fecha', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]))
                    ->toggle(),

                Tables\Filters\Filter::make('mes_actual')
                    ->label('Este Mes')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('fecha', Carbon::now()->month)
                        ->whereYear('fecha', Carbon::now()->year))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('justificar')
                    ->label('Justificar')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->visible(fn (Asistencia $record) => 
                        !$record->justificada && 
                        in_array($record->estado, ['Tarde', 'Ausente'])
                    )
                    ->form([
                        Textarea::make('justificacion')
                            ->label('Justificación')
                            ->required()
                            ->maxLength(500)
                            ->rows(4)
                            ->placeholder('Ingrese el motivo de la justificación...')
                    ])
                    ->action(function (Asistencia $record, array $data, AsistenciaService $service): void {
                        try {
                            // El usuario autenticado ES un Administrador directamente
                            $admin = Auth::user();
                            
                            if (!$admin || !$admin->id) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se pudo identificar al administrador')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $service->justificarAsistencia(
                                asistenciaId: $record->id,
                                justificacion: $data['justificacion'],
                                adminId: $admin->id
                            );

                            Notification::make()
                                ->title('Asistencia justificada')
                                ->body('La asistencia ha sido justificada correctamente')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al justificar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->successNotificationTitle('Asistencia justificada')
                    ->modalSubmitActionLabel('Justificar')
                    ->modalCancelActionLabel('Cancelar')
                    ->modalWidth('md'),

                Action::make('ver_detalles')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn (Asistencia $record) => view('filament.pages.detalle-asistencia', [
                        'asistencia' => $record,
                        'empleado' => $record->empleado,
                        'horario' => $record->empleado->horario,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('2xl'),
            ])
            ->bulkActions([])
            ->defaultSort('fecha', 'desc')
            ->poll('30s')
            ->emptyStateHeading('No hay registros de asistencia')
            ->emptyStateDescription('Las asistencias se registran automáticamente cuando los empleados marcan su huella.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistencias::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['empleado.sucursal', 'empleado.horario', 'huella']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
    
    public static function canDeleteAny(): bool
    {
        return false;
    }
}
