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
use Filament\Tables\Columns\ToggleColumn;
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
                TextColumn::make('modo_control')
                    ->label('Modo')
                    ->badge()
                    ->color(fn ($state) => $state === 'manual' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'manual' ? 'Manual' : 'Automático'),
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
                EditAction::make(),
                // Acción: Cambiar entre modo auto/manual
                Action::make('cambiar_modo_control')
                    ->label(fn ($record) => $record->modo_control === 'manual' ? 'Cambiar a automático' : 'Cambiar a manual')
                    ->icon(fn ($record) => $record->modo_control === 'manual' ? 'heroicon-o-cog' : 'heroicon-o-user')
                    ->color('secondary')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->modo_control === 'manual'
                        ? '¿Desea cambiar el modo de esta alarma a Automático?' 
                        : '¿Desea cambiar el modo de esta alarma a Manual?')
                    ->modalDescription(fn ($record) => $record->modo_control === 'manual'
                        ? 'Esto permitirá que el sistema cambie el estado automáticamente según el horario.' 
                        : 'Esto bloqueará el estado y solo lo podrá cambiar manualmente.')
                    ->action(function ($record) {
                        $record->modo_control = $record->modo_control === 'manual' ? 'auto' : 'manual';
                        $record->save();

                        Notification::make()
                            ->info()
                            ->title($record->modo_control === 'manual' ? 'Modo manual activado' : 'Modo automático activado')
                            ->send();
                    }),

                // Solo muestra las acciones manuales si el modo de control es manual
                Action::make('apagar')
                    ->label('Apagar')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->modo_control === 'manual' && in_array($record->estado, ['En Espera', 'Activa']))
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

                Action::make('en_espera')
                    ->label('Esperar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->modo_control === 'manual' && in_array($record->estado, ['Apagada', 'Activa']))
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

                Action::make('activar')
                    ->label('Activar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->modo_control === 'manual' && in_array($record->estado, ['En Espera', 'Apagada']))
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