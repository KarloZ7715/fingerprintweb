<?php

namespace App\Filament\Resources\AsistenciaDiarias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AsistenciaDiariaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('empleado.id')
                    ->label('Empleado'),
                TextEntry::make('horario.id')
                    ->label('Horario'),
                TextEntry::make('fecha')
                    ->date(),
                TextEntry::make('hora_entrada')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('hora_salida')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('minutos_retraso')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('horas_trabajadas')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('estado')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
