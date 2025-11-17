<?php

namespace App\Filament\Resources\AsistenciaDiarias;

use App\Filament\Resources\AsistenciaDiarias\Pages\CreateAsistenciaDiaria;
use App\Filament\Resources\AsistenciaDiarias\Pages\EditAsistenciaDiaria;
use App\Filament\Resources\AsistenciaDiarias\Pages\ListAsistenciaDiarias;
use App\Filament\Resources\AsistenciaDiarias\Pages\ViewAsistenciaDiaria;
use App\Filament\Resources\AsistenciaDiarias\Schemas\AsistenciaDiariaForm;
use App\Filament\Resources\AsistenciaDiarias\Schemas\AsistenciaDiariaInfolist;
use App\Filament\Resources\AsistenciaDiarias\Tables\AsistenciaDiariasTable;
use App\Models\AsistenciaDiaria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AsistenciaDiariaResource extends Resource
{
    protected static ?string $model = AsistenciaDiaria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    // Mostrar registros de HOY y orden descendente por id
    public static function getEloquentQuery(): Builder
    {
      return parent::getEloquentQuery()->orderBy('id', 'desc');

    }



    public static function table(Table $table): Table
    {
        return AsistenciaDiariasTable::configure($table);
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
            'index' => ListAsistenciaDiarias::route('/'),
            'create' => CreateAsistenciaDiaria::route('/create'),
            'view' => ViewAsistenciaDiaria::route('/{record}'),
            'edit' => EditAsistenciaDiaria::route('/{record}/edit'),
        ];
    }
}