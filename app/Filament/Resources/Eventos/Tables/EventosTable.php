<?php

namespace App\Filament\Resources\Eventos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_evento')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('Alarma.nombre')
                    ->label('Alarma asociada')
                    ->sortable()
                    ->searchable(),

                
                TextColumn::make('Accion')
                    ->searchable(),

                TextColumn::make('envios_forma')
                    ->label('Forma de envío')
                    ->getStateUsing(fn ($record) => 
                        $record->envios->pluck('forma')->implode(', ') ?: 'No disponible'
                    ),

                TextColumn::make('envios_estado')
                    ->label('Estado del envío')
                    ->getStateUsing(fn ($record) => 
                        $record->envios->pluck('estado')->implode(', ') ?: 'No disponible'
                    ),

                TextColumn::make('envios_contacto')
                    ->label('Enviado a')
                    ->getStateUsing(fn ($record) => 
                        $record->envios
                            ->map(fn ($envio) => $envio->contacto?->nombre_completo)
                            ->filter()
                            ->implode(', ') ?: 'No disponible'
                    ),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
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