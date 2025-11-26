<?php

namespace App\Filament\Resources\Alarmas;

use App\Filament\Resources\Alarmas\Pages\CreateAlarma;
use App\Filament\Resources\Alarmas\Pages\EditAlarma;
use App\Filament\Resources\Alarmas\Pages\ListAlarmas;
use App\Filament\Resources\Alarmas\Pages\ViewAlarma;
use App\Filament\Resources\Alarmas\Schemas\AlarmaForm;
use App\Filament\Resources\Alarmas\Schemas\AlarmaInfolist;
use App\Filament\Resources\Alarmas\Tables\AlarmasTable;
use App\Models\Alarma;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AlarmaResource extends Resource
{
    protected static ?string $model = Alarma::class;
protected static string|UnitEnum|null $navigationGroup = 'Gestión de Alarmas';
protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone   ;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AlarmaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AlarmaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlarmasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlarmas::route('/'),
            'create' => CreateAlarma::route('/create'),
            'view' => ViewAlarma::route('/{record}'),
            'edit' => EditAlarma::route('/{record}/edit'),
        ];
    }
}
