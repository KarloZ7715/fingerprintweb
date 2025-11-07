<?php

namespace App\Filament\Resources\Eventos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EventoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('fecha_evento'),
                TextInput::make('alarma_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('Evento')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('Accion')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
