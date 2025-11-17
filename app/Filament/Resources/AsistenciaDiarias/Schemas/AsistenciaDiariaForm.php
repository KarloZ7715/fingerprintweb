<?php

namespace App\Filament\Resources\AsistenciaDiarias\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AsistenciaDiariaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('empleado_id')
                    ->relationship('empleado', 'id')
                    ->required(),
                Select::make('horario_id')
                    ->relationship('horario', 'id')
                    ->required(),
                DatePicker::make('fecha')
                    ->required(),
                TimePicker::make('hora_entrada'),
                TimePicker::make('hora_salida'),
                TextInput::make('minutos_retraso')
                    ->numeric()
                    ->default(0),
                TextInput::make('horas_trabajadas')
                    ->numeric()
                    ->default(0.0),
                Select::make('estado')
                    ->options(['completo' => 'Completo', 'incompleto' => 'Incompleto', 'ausente' => 'Ausente'])
                    ->default('ausente'),
            ]);
    }
}
