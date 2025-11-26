<?php

namespace App\Filament\Resources\Alarmas\Schemas;

use App\Models\Alarma;
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
                    ->label('Nombre alarma')
                    ->required()
                    ->maxLength(50),

                Select::make('estado')
                    ->options([
                        Alarma::ESTADO_APAGADA => 'Apagada',
                        Alarma::ESTADO_EN_ESPERA => 'En Espera',
                        Alarma::ESTADO_ACTIVA => 'Activa',
                    ])
                    ->default(Alarma::ESTADO_APAGADA)
                    ->label('Estado')
                    ->required()
                    ->helperText('Apagada: desactivada | En Espera: esperando sensor de movimiento | Activa: sonando'),

                TextInput::make('duracion')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(60)
                    ->suffix('minutos')
                    ->label('Duración'),

                TimePicker::make('h_encendido')
                    ->required()
                    ->label('Hora encendido')
                    ->helperText('Hora en que la alarma puede activarse'),

                TimePicker::make('h_apagado')
                    ->required()
                    ->label('Hora apagado')
                    ->helperText('Hora en que la alarma se desactiva automáticamente'),

                Select::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal asignada')
                    ->required()
                    ->preload(),
            ]);
    }
}
