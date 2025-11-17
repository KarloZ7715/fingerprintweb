<?php

namespace App\Filament\Resources\Justificacions;

use App\Filament\Resources\Justificacions\Pages\CreateJustificacion;
use App\Filament\Resources\Justificacions\Pages\EditJustificacion;
use App\Filament\Resources\Justificacions\Pages\ListJustificacions;
use App\Filament\Resources\Justificacions\Pages\ViewJustificacion;
use App\Filament\Resources\Justificacions\Schemas\JustificacionForm;
use App\Filament\Resources\Justificacions\Schemas\JustificacionInfolist;
use App\Filament\Resources\Justificacions\Tables\JustificacionsTable;
use App\Models\Justificacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Filament\Schemas\Schema;

class JustificacionResource extends Resource
{
    protected static ?string $model = Justificacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    
public static function table(Table $table): Table
{
    return JustificacionsTable::configure($table)
        ->defaultSort('id', 'desc');
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
            'index' => ListJustificacions::route('/'),
            'create' => CreateJustificacion::route('/create'),
            'view' => ViewJustificacion::route('/{record}'),
            'edit' => EditJustificacion::route('/{record}/edit'),
        ];
    }
}
