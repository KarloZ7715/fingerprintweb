<?php

namespace App\Filament\Resources\Justificacions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class JustificacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('asistencia_diaria_id')
                    ->relationship('asistenciaDiaria', 'id')
                    ->required(),
                Select::make('empleado_id')
                    ->relationship('empleado', 'id')
                    ->required(),
                Select::make('tipo')
                    ->options(['retraso' => 'Retraso', 'ausencia' => 'Ausencia', 'salida_temprana' => 'Salida temprana'])
                    ->required(),
                Textarea::make('motivo')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('estado')
                    ->options(['pendiente' => 'Pendiente', 'aprobada' => 'Aprobada', 'rechazada' => 'Rechazada'])
                    ->default('pendiente'),
                TextInput::make('aprobado_por')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('fecha_aprobacion'),
            ]);
    }
}
