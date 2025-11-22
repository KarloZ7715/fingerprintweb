<?php

namespace App\Filament\Resources\Justificacions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JustificacionForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->components([
                // Selección de empleado, muestra nombre y apellido
                Select::make('empleado_id')
                    ->relationship('empleado', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->primer_nombre} {$record->primer_apellido}")
                    ->required(),

                // Tipo de justificación
                Select::make('tipo')
                    ->options([
               'incapacidad' => 'Incapacidad',
    'falta' => 'Falta',
    'entrada_tarde' => 'Entrada tarde',
    'salida_temprana' => 'Salida temprana',
    'vacaciones' => 'Vacaciones',
    'cambio_turno' => 'Cambio de turno',
    'retraso' => 'Retraso',
    'entrada_temprana' => 'Entrada temprana',
    'salida_tarde' => 'Salida tarde'



                    ])
                    ->required(),

                // Motivo
                Textarea::make('motivo')
                    ->default(null)
                    ->columnSpanFull(),

                // Estado
                Select::make('estado')
                    ->options([
                        'aprobada' => 'Aprobada',
                        'pendiente' => 'Pendiente',
                        'rechazada' => 'Rechazada'
                    ])
                    ->default('aprobada'),

                    //añade un fecha_incapacidad que no sea opcional que sea un caliendario
                TextInput::make('fecha_incapacidad')
            ->label('Fecha de la novedad')
                    ->type('date')
               ,
                // Campo oculto para enviar el id del aprobador
                Hidden::make('aprobado_por')
                    ->default(Auth::id())
                    ->dehydrated(true),

                // Mostrar el nombre del aprobador pero NO editable
                Placeholder::make('aprobado_nombre')
                    ->label('Aprobado por')
                    ->content($user ? "{$user->primer_nombre} {$user->primer_apellido}" : 'N/A'),

                // Campo oculto para la fecha de aprobación
                Hidden::make('fecha_aprobacion')
                    ->default(Carbon::now()->format('Y-m-d H:i:s'))
                    ->dehydrated(true),

                // Mostrar fecha de aprobación visible pero NO editable
                Placeholder::make('fecha_aprobacion_display')
                    ->label('Fecha de aprobación')
                    ->content(Carbon::now()->format('Y-m-d H:i')),

                // Plazo, opcional y mayor a 0 si se usa
                TextInput::make('plazo_dias')
                    ->label('Plazo (días)')
                    ->numeric()
                    ->minValue(1)
                    ->default(null)
                    ->required(false),
            ]);
    }
}