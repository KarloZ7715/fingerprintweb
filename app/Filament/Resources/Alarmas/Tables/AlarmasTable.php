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
                        ? '¿Desea apagar la alarma?'
                        : '¿Desea encender la alarma?'
                    )
                    ->action(function ($record) {
                        $nuevoEstado = $record->estado === 'Activa' ? 'Inactiva' : 'Activa';
                        $record->estado = $nuevoEstado;
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title(
                                $nuevoEstado === 'Activa'
                                    ? 'Alarma encendida correctamente'
                                    : 'Alarma apagada correctamente'
                            )
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
