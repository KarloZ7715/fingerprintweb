<?php

namespace App\Filament\Resources\Alarmas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Models\Evento;
use Carbon\Carbon;

class AlarmasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->label('Nombre alarma'),
                TextColumn::make('estado')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Puedes adaptar la presentación del estado si lo deseas
                        if ($state === 'Apagada') {
                            return 'Apagada';
                        }
                        if ($state === 'En Espera') {
                            return 'En espera';
                        }
                        if ($state === 'Activa') {
                            return 'Sonando';
                        }
                        return $state;
                    }),
                TextColumn::make('duracion')
                    ->numeric()
                    ->sortable()
                    ->label('Duración (min)'),
                TextColumn::make('h_encendido')
                    ->time()
                    ->sortable()
                    ->label('Hora encendido'),
                TextColumn::make('h_apagado')
                    ->time()
                    ->sortable()
                    ->label('Hora apagado'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Puedes agregar filtros si lo necesitas
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('accionar')
                    ->label(fn($record) => match ($record->estado) {
                        'Activa' => 'Apagar',
                        'Apagada' => 'Esperar',
                        'En Espera' => 'Activar',
                        default => 'Cambiar Estado'
                    })
                    ->color(fn($record) => match ($record->estado) {
                        'Activa' => 'danger',
                        'Apagada' => 'info',
                        'En Espera' => 'success',
                        default => 'primary'
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => match ($record->estado) {
                        'Activa' => '¿Desea apagar la alarma manualmente?',
                        'Apagada' => '¿Desea poner la alarma en espera?',
                        'En Espera' => '¿Desea activar la alarma?',
                        default => '¿Desea cambiar el estado de la alarma?'
                    })
                    ->action(function ($record) {
                        $nuevoEstado = match ($record->estado) {
                            'Activa' => 'Apagada',     
                            'Apagada' => 'En Espera',  
                            'En Espera' => 'Activa',   
                            default => 'Apagada'
                        };

                        $accion = match ($nuevoEstado) {
                            'Apagada' => 'Alarma apagada manualmente',
                            'En Espera' => 'Alarma puesta en espera manualmente',
                            'Activa' => 'Alarma activada manualmente',
                            default => 'Estado cambiado manualmente'
                        };

                        $eventoTipo = match ($nuevoEstado) {
                            'Apagada' => 'Desactivar',
                            'En Espera' => 'Esperar',
                            'Activa' => 'Activar',
                            default => 'Cambio'
                        };

                        $record->estado = $nuevoEstado;
                        $record->save();

                        // Registrar evento en la BD
                        $fechaEnvio = Carbon::now('America/Bogota');

                        Evento::create([
                            'alarma_id' => $record->id,
                            'fecha_evento' => $fechaEnvio,
                            'Evento' => $eventoTipo,
                            'Accion' => $accion,
                        ]);

                        Notification::make()
                            ->success()
                            ->title($accion)
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
