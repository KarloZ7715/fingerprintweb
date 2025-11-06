<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use BackedEnum;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Registrar Empleado';
    protected static ?string $pluralModelLabel = 'Empleados';
    protected static ?string $modelLabel = 'Empleado';

    /**
     * 🔹 Esquema del formulario (nuevo formato Filament 4)
     */
    public static function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('cedula')
                ->label('Cédula')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('primer_nombre')
                ->label('Primer Nombre')
                ->required(),

            Forms\Components\TextInput::make('primer_apellido')
                ->label('Primer Apellido')
                ->required(),

            Forms\Components\TextInput::make('telefono')
                ->label('Teléfono'),

            Forms\Components\Select::make('estado')
                ->label('Estado')
                ->options([
                    'Activo' => 'Activo',
                    'Inactivo' => 'Inactivo',
                ])
                ->default('Activo'),

            Forms\Components\TextInput::make('sucursal_id')
                ->label('Sucursal ID')
                ->numeric()
                ->required(),

            // Campo oculto donde guardaremos temporalmente el ID de huella
            Forms\Components\Hidden::make('fingerprint_id'),
        ];
    }

    /**
     * 🔹 Columnas de la tabla (nuevo formato Filament 4)
     */
    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('cedula')->searchable(),
            Tables\Columns\TextColumn::make('primer_nombre'),
            Tables\Columns\TextColumn::make('primer_apellido'),
            Tables\Columns\TextColumn::make('telefono'),
            Tables\Columns\TextColumn::make('estado'),
            Tables\Columns\TextColumn::make('huella.codigo_huella')->label('Huella'),
        ];
    }

    /**
     * 🔹 Páginas asociadas al recurso
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
        ];
    }
}
