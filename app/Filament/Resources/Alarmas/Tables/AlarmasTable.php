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
use Illuminate\Support\Facades\Auth;
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
                    ->searchable(),
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
                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal asignada')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('accionar')
                    ->label(fn ($record) => $record->estado === 'Activa' ? 'Apagar' : 'Encender')
                    ->color(fn ($record) => $record->estado === 'Activa' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->estado === 'Activa'
                        ? '¿Desea apagar la alarma manualmente?'
                        : '¿Desea encender la alarma manualmente?'
                    )
                    ->action(function ($record) {
                        $nuevoEstado = $record->estado === 'Activa' ? 'Inactiva' : 'Activa';
                        $accion = $nuevoEstado === 'Activa'
                            ? 'Alarma encendida manualmente'
                            : 'Alarma apagada manualmente';

                        $record->estado = $nuevoEstado;
                        $record->save();

                        // Registrar evento en la BD
                                $fechaEnvio = Carbon::now('America/Bogota');

                        Evento::create([
                            'alarma_id' => $record->id,
                            'fecha_evento' =>$fechaEnvio,
                            'Evento' => $nuevoEstado === 'Activa' ? 'Activar' : 'Desactivar',
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