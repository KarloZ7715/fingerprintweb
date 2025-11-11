<?php

namespace App\Filament\Resources\Eventos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fecha_evento')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('alarma_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('Evento')
                    ->columnSpanFull(),
                TextEntry::make('Accion')
                    ->columnSpanFull(),
            ]);
    }
}
