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
                // Puedes agregar filtros aquí si lo necesitas
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // Acción: Apagar (solo si está En Espera o Activa)
                Action::make('apagar')
                    ->label('Apagar')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->estado, ['En Espera', 'Activa']))
                    ->modalHeading('¿Desea apagar la alarma manualmente?')
                    ->action(function ($record) {
                        $record->estado = 'Apagada';
                        $record->save();

                        $fechaEnvio = Carbon::now('America/Bogota');
                        Evento::create([
                            'alarma_id' => $record->id,
                            'fecha_evento' => $fechaEnvio,
                            'Evento' => 'Desactivar',
                            'Accion' => 'Alarma apagada manualmente',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Alarma apagada manualmente')
                            ->send();
                    }),

                // Acción: Poner en espera (solo si está Apagada o Activa)
                Action::make('en_espera')
                    ->label('Esperar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->estado, ['Apagada', 'Activa']))
                    ->modalHeading('¿Desea poner la alarma en espera?')
                    ->action(function ($record) {
                        $record->estado = 'En Espera';
                        $record->save();

                        $fechaEnvio = Carbon::now('America/Bogota');
                        Evento::create([
                            'alarma_id' => $record->id,
                            'fecha_evento' => $fechaEnvio,
                            'Evento' => 'Esperar',
                            'Accion' => 'Alarma puesta en espera manualmente',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Alarma puesta en espera manualmente')
                            ->send();
                    }),

                // Acción: Activar (solo si está En Espera o Apagada)
                Action::make('activar')
                    ->label('Activar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->estado, ['En Espera', 'Apagada']))
                    ->modalHeading('¿Desea activar la alarma?')
                    ->action(function ($record) {
                        $record->estado = 'Activa';
                        $record->save();

                        $fechaEnvio = Carbon::now('America/Bogota');
                        Evento::create([
                            'alarma_id' => $record->id,
                            'fecha_evento' => $fechaEnvio,
                            'Evento' => 'Activar',
                            'Accion' => 'Alarma activada manualmente',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Alarma activada manualmente')
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