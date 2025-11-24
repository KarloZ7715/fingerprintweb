<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ManualUsuarioWidget extends Widget
{
    protected string $view = 'filament.widgets.manual-usuario-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 2;

    public function getManualUrl(): string
    {
        return asset('storage/documentos/manual-usuario.pdf');
    }
}
