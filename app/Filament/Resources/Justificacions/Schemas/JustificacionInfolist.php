<?php

namespace App\Filament\Resources\Justificacions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JustificacionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('asistenciaDiaria.id')
                    ->label('Asistencia diaria'),
                TextEntry::make('empleado.id')
                    ->label('Empleado'),
                TextEntry::make('tipo')
                    ->badge(),
                TextEntry::make('motivo')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('estado')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('aprobado_por')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('fecha_aprobacion')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
