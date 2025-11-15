<?php

namespace App\Filament\Widgets;

use App\Models\Asistencia;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AsistenciasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected  ?string $pollingInterval = '30s';
    
    // No mostrar este widget en el dashboard principal
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $hoy = Carbon::now('America/Bogota')->toDateString();
        
        $totalHoy = Asistencia::where('fecha', $hoy)->count();
        $puntuales = Asistencia::where('fecha', $hoy)->where('estado', 'Puntual')->count();
        $tardes = Asistencia::where('fecha', $hoy)->where('estado', 'Tarde')->count();
        $ausentes = Asistencia::where('fecha', $hoy)->where('estado', 'Ausente')->count();

        // Calcular porcentaje de puntualidad
        $porcentajePuntualidad = $totalHoy > 0 ? round(($puntuales / $totalHoy) * 100, 1) : 0;

        return [
            Stat::make('Asistencias Hoy', $totalHoy)
                ->description('Total de registros del día')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->chart([7, 12, 18, 15, 22, 18, $totalHoy]),

            Stat::make('Puntuales', $puntuales)
                ->description($porcentajePuntualidad . '% de puntualidad')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([$puntuales, $puntuales - 2, $puntuales + 1, $puntuales, $puntuales + 3]),

            Stat::make('Tardanzas', $tardes)
                ->description($tardes > 0 ? 'Requieren atención' : 'Sin tardanzas')
                ->descriptionIcon('heroicon-m-clock')
                ->color($tardes > 0 ? 'warning' : 'success')
                ->chart([$tardes + 2, $tardes, $tardes + 1, $tardes, $tardes]),

            Stat::make('Ausentes', $ausentes)
                ->description($ausentes > 0 ? '¡Requiere acción!' : 'Sin ausencias')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($ausentes > 0 ? 'danger' : 'success')
                ->chart([$ausentes + 1, $ausentes, $ausentes + 2, $ausentes, $ausentes]),
        ];
    }
}
