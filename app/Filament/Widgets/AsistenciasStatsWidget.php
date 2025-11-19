<?php

namespace App\Filament\Widgets;

use App\Models\AsistenciaDiaria;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AsistenciasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '30s';
    
    // No mostrar este widget en el dashboard principal
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        // Obtener fecha actual en zona horaria de Bogotá desde servidor en Francia
        $fechaBogota = DB::selectOne("
            SELECT DATE(CONVERT_TZ(NOW(), @@session.time_zone, 'America/Bogota')) as fecha_bogota
        ")->fecha_bogota;

        // Total de asistencias del día (con hora_entrada registrada)
        $totalHoy = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM asistencia_diaria ad
            WHERE ad.fecha = ?
              AND ad.hora_entrada IS NOT NULL
        ", [$fechaBogota])->total ?? 0;

        // Inasistencias (empleados activos con horario que NO registraron entrada)
        $ausentes = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM empleado e
            WHERE e.estado = 'Activo'
              AND e.horario_id IS NOT NULL
              AND EXISTS (
                  SELECT 1
                  FROM asistencia_diaria ad2
                  WHERE ad2.horario_id = e.horario_id
                    AND ad2.fecha = ?
                    AND ad2.hora_entrada IS NOT NULL
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM asistencia_diaria ad
                  WHERE ad.empleado_id = e.id
                    AND ad.fecha = ?
                    AND ad.hora_entrada IS NOT NULL
              )
        ", [$fechaBogota, $fechaBogota])->total ?? 0;

        return [
            Stat::make('Asistencias Hoy', $totalHoy)
                ->description('Total de registros del día')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->chart([7, 12, 18, 15, 22, 18, $totalHoy]),

            Stat::make('Ausentes', $ausentes)
                ->description($ausentes > 0 ? '¡Requiere acción!' : 'Sin ausencias')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($ausentes > 0 ? 'danger' : 'success')
                ->chart([$ausentes + 1, $ausentes, $ausentes + 2, $ausentes, $ausentes]),
        ];
    }
}
