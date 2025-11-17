<?php

namespace App\Filament\Resources\Justificacions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Models\Administrador;
use App\Models\AsistenciaDiaria;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;

class JustificacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
           
                // Empleado: nombre, apellido y cédula
                TextColumn::make('empleado.primer_nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('empleado.primer_apellido')
                    ->label('Apellido')
                    ->searchable(),
                TextColumn::make('empleado.cedula')
                    ->label('Cédula')
                    ->searchable(),

                TextColumn::make('tipo')
                    ->badge(),
                TextColumn::make('estado')
                    ->badge(),
                // Mostrar nombre del administrador (aprobado_por)
                TextColumn::make('administrador.primer_nombre')
                    ->label('Aprobado por')
                    ->sortable()
                    ->searchable(),
                           TextColumn::make('motivo')
                    ->badge(),
                TextColumn::make('fecha_aprobacion')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                // Buscar por cédula de empleado
                Filter::make('cedula')
                    ->form([
                        TextInput::make('cedula')->label('Cédula empleado'),
                    ])
                    ->query(function ($query, $data) {
                        return !empty($data['cedula'])
                            ? $query->whereHas('empleado', fn($q) => $q->where('cedula', $data['cedula']))
                            : $query;
                    }),
                // Filtrar por fecha de creación
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_at')->label('Fecha de creación'),
                    ])
                    ->query(function ($query, $data) {
                        return !empty($data['created_at'])
                            ? $query->whereDate('created_at', $data['created_at'])
                            : $query;
                    }),
                SelectFilter::make('tipo')
    ->label('Tipo')
    ->options([
        'retraso' => 'Retraso',
        'ausencia' => 'Ausencia',
        'salida_temprana' => 'Salida Temprana',
        'entro_tarde' => 'Entró Tarde',
        'entro_temprano' => 'Entró Temprano',
        'salio_tarde' => 'Salió Tarde',
        'salio_temprano' => 'Salió Temprano',
        'falta' => 'Falta',
    ])
    ->default('falta')
    ->query(function ($query, $value) {
        return !empty($value)
            ? $query->where('tipo', $value)
            : $query;
    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Acción: aprobar
                Action::make('Aprobar')
                    ->color('success')
                    ->visible(fn($record) => $record->estado === 'pendiente')
                    ->action(function ($record) {
                        $adminId = Auth::id(); // Usar tu modelo Admin
                        $record->estado = 'aprobada';
                        $record->aprobado_por = $adminId;
                        $record->fecha_aprobacion = Carbon::now();
                        $record->save();
                    }),
                // Acción: rechazar
                Action::make('Rechazar')
                    ->color('danger')
                    ->visible(fn($record) => $record->estado === 'pendiente')
                    ->action(function ($record) {
                        $adminId = Auth::id();
                        $record->estado = 'rechazada';
                        $record->aprobado_por = $adminId;
                        $record->fecha_aprobacion = Carbon::now();
                        $record->save();
                    }),
                // Modal para registrar/editar motivo
                Action::make('Motivo')
                    ->modalHeading('Registrar motivo')
                    ->modalSubmitActionLabel('Guardar motivo')
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo de la justificación')
                            ->required(),
                    ])
                    ->fillForm(fn($record) => ['motivo' => $record->motivo])
                    ->action(function ($record, $data) {
                        $record->motivo = $data['motivo'];
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
