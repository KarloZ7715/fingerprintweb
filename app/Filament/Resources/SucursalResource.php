<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SucursalResource\Pages;
use App\Models\Sucursal;
use App\Models\Administrador;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class SucursalResource extends Resource
{
    protected static ?string $model = Sucursal::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Sucursales';
    protected static ?string $pluralModelLabel = 'Sucursales';
    protected static ?string $modelLabel = 'Sucursal';
    protected static UnitEnum|string|null $navigationGroup = 'Gestión de Personal';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información de la Sucursal')
                    ->description('Datos básicos de la sucursal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre de la Sucursal')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ej: Sucursal Centro, Sucursal Northgate')
                                    ->helperText('Nombre identificador de la sucursal')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('direccion')
                                    ->label('Dirección')
                                    ->required()
                                    ->maxLength(255)
                                    ->rows(3)
                                    ->placeholder('Calle, número, barrio, ciudad')
                                    ->helperText('Dirección completa de la sucursal')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Administrador Responsable')
                    ->description('Asigna el administrador encargado de esta sucursal')
                    ->schema([
                        Forms\Components\Select::make('administrador_id')
                            ->label('Administrador')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(
                                Administrador::all()->mapWithKeys(fn($admin) => [
                                    $admin->id => $admin->getFilamentName() . ' (' . $admin->email . ')'
                                ])
                            )
                            ->helperText('Selecciona el administrador responsable')
                            ->afterStateUpdated(function ($state) {
                                // Feedback visual cuando se selecciona un administrador
                            }),
                    ])
                    ->collapsible(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn(Sucursal $record) => $record->direccion),

                Tables\Columns\TextColumn::make('administrador.getFilamentName')
                    ->label('Administrador')
                    ->getStateUsing(fn(Sucursal $record) => $record->administrador_nombre)
                    ->searchable(['administrador.primer_nombre', 'administrador.primer_apellido', 'administrador.email'])
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('administrador.email')
                    ->label('Email Administrador')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('empleados_count')
                    ->label('Empleados')
                    ->counts('empleados')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->timezone('America/Bogota'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('administrador_id')
                    ->label('Administrador')
                    ->relationship('administrador', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getFilamentName())
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Registrado desde')
                            ->timezone('America/Bogota')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Registrado hasta')
                            ->timezone('America/Bogota')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                BulkAction::make('asignar_administrador')
                    ->label('Asignar Administrador')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('administrador_id')
                            ->label('Nuevo Administrador')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(
                                Administrador::all()->mapWithKeys(fn($admin) => [
                                    $admin->id => $admin->getFilamentName()
                                ])
                            )
                            ->native(false),
                    ])
                    ->action(function ($records, array $data): void {
                        foreach ($records as $record) {
                            $record->update(['administrador_id' => $data['administrador_id']]);
                        }
                    })
                    ->successNotificationTitle('Administrador asignado correctamente')
                    ->color('warning'),

                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSucursales::route('/'),
            'create' => Pages\CreateSucursal::route('/create'),
            'edit' => Pages\EditSucursal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withCount('empleados');
    }
}
