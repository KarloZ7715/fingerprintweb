<?php

namespace App\Filament\Resources\Justificacions\Tables;
use Filament\Forms\Components\Select;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Models\Administrador;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;

class JustificacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Solo columnas esenciales en la tabla
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha registro')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('empleado.primer_nombre')
                    ->label('Nombre')
                    ->searchable(),
                     TextColumn::make('empleado.primer_apellido')
                    ->label('Apellido')
                    ->searchable(),
                TextColumn::make('empleado.cedula')
                    ->label('Cédula')
                    ->searchable(),
                             TextColumn::make('tipo')->badge(),
                TextColumn::make('estado')->badge(),
            ])
            ->filters([
                Filter::make('cedula')
                    ->form([
                        TextInput::make('cedula')->label('Cédula empleado'),
                    ])
                    ->query(function ($query, $data) {
                        return !empty($data['cedula'])
                            ? $query->whereHas('empleado', fn($q) => $q->where('cedula', $data['cedula']))
                            : $query;
                    }),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_at')->label('Fecha de creación'),
                    ])
                    ->query(function ($query, $data) {
                        return !empty($data['created_at'])
                            ? $query->whereDate('created_at', $data['created_at'])
                            : $query;
                    }),
            ])
            ->recordActions([
                // Acción: modal con todos los detalles
              Action::make('Ver detalles')
    ->modalHeading('Detalles de la justificación')
    ->modalContent(fn($record) => view('filament.justificacion-detalle', ['record' => $record])),

                // Acción: aprobar con motivo opcional
               Action::make('Aprobar')
    ->color('success')
    ->visible(fn($record) => $record->estado === 'pendiente')
    ->modalHeading('Detalles para aprobación')
    ->modalSubmitActionLabel('Aprobar')
    ->form([
        Select::make('tipo')
            ->label('Tipo de novedad')
            ->options([
                'incapacidad' => 'Incapacidad',
                'falta' => 'Falta',
                'entrada_tarde' => 'Entrada tarde',
                'salida_temprana' => 'Salida temprana',
                'vacaciones' => 'Vacaciones',
                'cambio_turno' => 'Cambio de turno',
                'retraso' => 'Retraso',
                'entrada_temprana' => 'Entrada temprana',
                'salida_tarde' => 'Salida tarde'
            ])
            ->required(),

        DatePicker::make('fecha_incapacidad')
            ->label('Fecha de inicio de la novedad')
            ->required(), // Puedes hacerlo opcional si lo prefieres

        TextInput::make('plazo_dias')
            ->label('Duración (días)')
            ->numeric()
            ->minValue(1)
            ->required(), // Puedes hacerlo opcional si lo prefieres

        Textarea::make('motivo')
            ->label('Motivo de la aprobación')
    ])
    ->action(function ($record, $data) {
        $adminId = Auth::id();
        $record->estado = 'aprobada';
        $record->aprobado_por = $adminId;
        $record->tipo = $data['tipo'] ?? $record->tipo;
        $record->motivo = $data['motivo'] ?? null;
        $record->fecha_aprobacion = Carbon::now();
        $record->fecha_incapacidad = $data['fecha_incapacidad'] ?? null;
        $record->plazo_dias = $data['plazo_dias'] ?? null;
        $record->save();
    }),

                // Acción: rechazar con motivo opcional
                Action::make('Rechazar')
                    ->color('danger')
                    ->visible(fn($record) => $record->estado === 'pendiente')
                    ->modalHeading('Motivo de rechazo')
                    ->modalSubmitActionLabel('Rechazar')
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo del rechazo')
                    ])
                    ->action(function ($record, $data) {
                        $adminId = Auth::id();
                        $record->estado = 'rechazada';
                        $record->aprobado_por = $adminId;
                        $record->motivo = $data['motivo'] ?? null;
                        $record->fecha_aprobacion = Carbon::now();
                        $record->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Puedes añadir acciones grupales si lo deseas
                ]),
            ]);
    }
}