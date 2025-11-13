<?php

namespace App\Filament\Resources\ContactoEmergencias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class ContactoEmergenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_completo')
                    ->default(null),
                TextInput::make('telefono')
                    ->tel()
                    ->default(null),
                TextInput::make('correo')
                    ->default(null),
                TextInput::make('usario_tele')
                    ->default(null)
                     ->label('Usuario de Telegram'),
                Select::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal asignada')
                    ->required(),
            ]);
    }
}
