<?php

namespace App\Filament\Resources\ContactoEmergencias;

use App\Filament\Resources\ContactoEmergencias\Pages\CreateContactoEmergencia;
use App\Filament\Resources\ContactoEmergencias\Pages\EditContactoEmergencia;
use App\Filament\Resources\ContactoEmergencias\Pages\ListContactoEmergencias;
use App\Filament\Resources\ContactoEmergencias\Pages\ViewContactoEmergencia;
use App\Filament\Resources\ContactoEmergencias\Schemas\ContactoEmergenciaForm;
use App\Filament\Resources\ContactoEmergencias\Schemas\ContactoEmergenciaInfolist;
use App\Filament\Resources\ContactoEmergencias\Tables\ContactoEmergenciasTable;
use App\Models\ContactoEmergencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactoEmergenciaResource extends Resource
{
    protected static ?string $model = ContactoEmergencia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ContactoEmergenciaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactoEmergenciaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactoEmergenciasTable::configure($table);
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
            'index' => ListContactoEmergencias::route('/'),
            'create' => CreateContactoEmergencia::route('/create'),
            'view' => ViewContactoEmergencia::route('/{record}'),
            'edit' => EditContactoEmergencia::route('/{record}/edit'),
        ];
    }
}
