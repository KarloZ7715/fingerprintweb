<?php

namespace App\Filament\Resources\ContactoEmergencias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactoEmergenciaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre_completo')
                    ->placeholder('-'),
                TextEntry::make('telefono')
                    ->placeholder('-'),
                TextEntry::make('correo')
                    ->placeholder('-'),
                TextEntry::make('usario_tele')
                    ->placeholder('-')
                    ->label('Usuario de Telegram'),
                TextEntry::make('sucursal.nombre')
                    ->label('Sucursal asignada')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
