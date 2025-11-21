<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReportesAsistencia extends Component
{
    public $mes;
    public $anio;
    public $reporteAsistencias = [];
    public $reporteRetrasos = [];
    public $reporteAusencias = [];

    public function mount()
    {
        $this->mes = now()->month;
        $this->anio = now()->year;
        $this->generarReportes();
    }

    public function generarReportes()
    {
        // 1. REPORTE ASISTENCIAS
        $this->reporteAsistencias = DB::select("
            SELECT 
                e.id,
                e.cedula,
                CONCAT(e.primer_nombre, ' ', e.primer_apellido) as empleado,
                COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END) as asistencias,
                (
                    SELECT COUNT(DISTINCT fecha)
                    FROM asistencia_diaria
                    WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                        AND horario_id = e.horario_id
                        AND estado IN ('completo', 'incompleto')
                        AND fecha >= e.created_at
                ) as dias_laborados,
                (
                    (
                        SELECT COUNT(DISTINCT fecha)
                        FROM asistencia_diaria
                        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                            AND horario_id = e.horario_id
                            AND estado IN ('completo', 'incompleto')
                            AND fecha >= e.created_at
                    ) - COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END)
                ) as ausencias,
                CASE 
                    WHEN (
                        SELECT COUNT(DISTINCT fecha)
                        FROM asistencia_diaria
                        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                            AND horario_id = e.horario_id
                            AND estado IN ('completo', 'incompleto')
                            AND fecha >= e.created_at
                    ) = 0 THEN 0
                    ELSE ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
                                AND fecha >= e.created_at
                        )
                    , 1)
                END as tasa,
                CASE 
                    WHEN (
                        SELECT COUNT(DISTINCT fecha)
                        FROM asistencia_diaria
                        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                            AND horario_id = e.horario_id
                            AND estado IN ('completo', 'incompleto')
                            AND fecha >= e.created_at
                    ) = 0 THEN 'CRÍTICO'
                    WHEN ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
                                AND fecha >= e.created_at
                        )
                    , 1) >= 95 THEN 'EXCELENTE'
                    WHEN ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
                                AND fecha >= e.created_at
                        )
                    , 1) >= 90 THEN 'ALERTA'
                    ELSE 'CRÍTICO'
                END as estado
            FROM empleado e
            LEFT JOIN asistencia_diaria ad ON e.id = ad.empleado_id 
                AND MONTH(ad.fecha) = ? AND YEAR(ad.fecha) = ?
            WHERE e.estado = 'Activo'
            GROUP BY e.id, e.cedula, e.primer_nombre, e.primer_apellido, e.horario_id, e.created_at
            ORDER BY tasa DESC
        ", [
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
            $this->mes, $this->anio,
        ]);

        // 2. REPORTE RETRASOS
        $this->reporteRetrasos = DB::select("
            SELECT 
                e.cedula,
                CONCAT(e.primer_nombre, ' ', e.primer_apellido) as empleado,
                COUNT(DISTINCT ad.fecha) as cantidad,
                ROUND(AVG(ad.minutos_retraso), 1) as promedio,
                MAX(ad.minutos_retraso) as maximo
            FROM empleado e
            JOIN asistencia_diaria ad ON e.id = ad.empleado_id
            WHERE ad.minutos_retraso > 0
                AND MONTH(ad.fecha) = ? AND YEAR(ad.fecha) = ?
                AND ad.fecha >= e.created_at
                AND e.estado = 'Activo'
            GROUP BY e.id, e.cedula, e.primer_nombre, e.primer_apellido, e.created_at
            ORDER BY cantidad DESC
        ", [$this->mes, $this->anio]);

        // 3. REPORTE AUSENCIAS
        $this->reporteAusencias = DB::select("
            SELECT 
                e.id,
                e.cedula,
                CONCAT(e.primer_nombre, ' ', e.primer_apellido) as empleado,
                (
                    SELECT COUNT(DISTINCT fecha)
                    FROM asistencia_diaria
                    WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                        AND horario_id = e.horario_id
                        AND estado IN ('completo', 'incompleto')
                        AND fecha >= e.created_at
                ) - COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') AND ad.fecha >= e.created_at THEN ad.fecha END) as total_ausencias
            FROM empleado e
            LEFT JOIN asistencia_diaria ad ON e.id = ad.empleado_id 
                AND MONTH(ad.fecha) = ? AND YEAR(ad.fecha) = ?
            WHERE e.estado = 'Activo'
            GROUP BY e.id, e.cedula, e.primer_nombre, e.primer_apellido, e.horario_id, e.created_at
            HAVING total_ausencias > 0
            ORDER BY total_ausencias DESC
        ", [
            $this->mes, $this->anio,
            $this->mes, $this->anio
        ]);

        // 4. DESPACHAR EVENTO PARA ACTUALIZAR GRÁFICOS
        $this->dispatch('actualizarGraficos', [
            'asistencias' => $this->prepararDatosAsistencias(),
            'ausencias' => $this->prepararDatosAusencias(),
            'retrasos' => $this->prepararDatosRetrasos(),
        ]);
    }

    public function prepararDatosAsistencias()
    {
        $topAsistencias = array_slice($this->reporteAsistencias, 0, 10);
        return [
            'labels' => array_map(fn($r) => $r->empleado, $topAsistencias),
            'data' => array_map(fn($r) => $r->tasa ?? 0, $topAsistencias),
            'backgroundColor' => array_map(function($r) {
                if (($r->tasa ?? 0) >= 95) return 'rgba(34, 197, 94, 0.7)';
                if (($r->tasa ?? 0) >= 90) return 'rgba(251, 146, 60, 0.7)';
                return 'rgba(239, 68, 68, 0.7)';
            }, $topAsistencias),
        ];
    }

    public function prepararDatosAusencias()
    {
        return [
            'labels' => array_map(fn($r) => $r->empleado, $this->reporteAusencias),
            'data' => array_map(fn($r) => $r->total_ausencias, $this->reporteAusencias),
        ];
    }

    public function prepararDatosRetrasos()
    {
        $topRetrasos = array_slice($this->reporteRetrasos, 0, 10);
        return [
            'labels' => array_map(fn($r) => $r->empleado, $topRetrasos),
            'data' => array_map(fn($r) => $r->promedio, $topRetrasos),
        ];
    }

    public function descargarPDF()
    {
        return redirect()->route('reportes.pdf', [
            'mes' => $this->mes,
            'anio' => $this->anio,
        ]);
    }

    public function render()
    {
        return view('livewire.reportes-asistencia');
    }
}