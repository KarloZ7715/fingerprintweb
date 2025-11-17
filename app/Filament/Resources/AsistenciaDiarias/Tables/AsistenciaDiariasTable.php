<?php

namespace App\Filament\Resources\AsistenciaDiarias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AsistenciaDiariasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('empleado.primer_nombre')
                    ->label('Primer Nombre')
                    ->searchable(),
                TextColumn::make('empleado.primer_apellido')
                    ->label('Primer Apellido')
                    ->searchable(),
                TextColumn::make('horario.nombre')
                    ->label('Horario')
                    ->searchable(),
                TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('hora_entrada')
                    ->time()
                    ->sortable(),
                TextColumn::make('hora_salida')
                    ->time()
                    ->sortable(),
                TextColumn::make('minutos_retraso')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('horas_trabajadas')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtrar solo los registros de hoy
                Filter::make('today')
                    ->label('Solo Hoy')
                    ->query(fn (Builder $query) =>
                        $query->whereDate('fecha', Carbon::today())
                    ),
                // Filtrar por fecha personalizada
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('fecha')->label('Por Fecha'),
                    ])
                    ->query(fn (Builder $query, $data) =>
                        $data['fecha']
                            ? $query->whereDate('fecha', $data['fecha'])
                            : $query
                    ),
                // Filtrar por cédula del empleado
                Filter::make('cedula')
                    ->label('Por cédula')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('cedula')->label('Cédula'),
                    ])
                    ->query(fn (Builder $query, $data) =>
                        $data['cedula']
                            ? $query->whereHas('empleado', fn ($q) => $q->where('cedula', $data['cedula']))
                            : $query
                    ),
                // Filtrar por estado de la asistencia
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Presente' => 'Presente',
                        'Ausente' => 'Ausente',
                        'Justificado' => 'Justificado',
                    ])
                    ->query(fn (Builder $query, $value) =>
                        $value
                            ? $query->where('estado', $value)
                            : $query
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}