<?php

namespace App\Filament\Resources\Alarmas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AlarmaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre')
                    ->placeholder('-'),
                TextEntry::make('estado')
                    ->placeholder('-'),
                TextEntry::make('duracion')
                    ->numeric(),
                TextEntry::make('h_encendido')
                    ->time(),
                TextEntry::make('h_apagado')
                    ->time(),
                TextEntry::make('sucursal_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
