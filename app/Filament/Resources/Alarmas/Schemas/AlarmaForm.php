<?php

namespace App\Filament\Resources\Alarmas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AlarmaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->default(null)
                    ->label('Nombre alarma'),
                TextInput::make('estado')
                    ->default("Inactiva")
                    ->label('Estado')
                    ->disabled(), // Solo lectura
                TextInput::make('duracion')
                    ->required()
                    ->numeric()
                    ->label('Duración (min)'),
                TimePicker::make('h_encendido')
                    ->required()
                    ->label('Hora encendido'),
                TimePicker::make('h_apagado')
                    ->required()
                    ->label('Hora apagado'),
                Select::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal asignada')
                    ->required(),
            ]);
    }
}
