<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdministradorResource\Pages;
use App\Models\Administrador;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class AdministradorResource extends Resource
{
    protected static ?string $model = Administrador::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Administradores';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuracion';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('primer_nombre')
                    ->label('Primer nombre')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('primer_apellido')
                    ->label('Primer apellido')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('cedula')
                    ->label('Cedula')
                    ->maxLength(20)
                    ->helperText('Valor opcional, se recomienda mantenerlo unico.'),
                Forms\Components\TextInput::make('telefono')
                    ->label('Telefono')
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label('Correo electronico')
                    ->email()
                    ->required()
                    ->helperText('Se usa para iniciar sesion en el panel.'),
                Forms\Components\TextInput::make('password')
                    ->label('Contrasena')
                    ->password()
                    ->revealable()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn(?string $state): bool => filled($state)),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('primer_nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('primer_apellido')
                    ->label('Apellido')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdministradors::route('/'),
            'create' => Pages\CreateAdministrador::route('/create'),
            'edit' => Pages\EditAdministrador::route('/{record}/edit'),
        ];
    }
}
