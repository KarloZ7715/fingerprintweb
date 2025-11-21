<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportePDFController extends Controller
{
    public function generarPDF($mes, $anio)
    {
        // Reporte Asistencias ajustado para nuevos empleados (solo cuenta desde ingreso)
        $reporteAsistencias = DB::select("
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
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
            $mes, $anio,
        ]);
        
        // Reporte de retrasos ajustado (solo incluye desde fecha de ingreso)
        $reporteRetrasos = DB::select("
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
        ", [$mes, $anio]);

        $data = [
            'reporteAsistencias' => $reporteAsistencias,
            'reporteRetrasos' => $reporteRetrasos,
            'mes' => $mes,
            'anio' => $anio,
        ];

        $pdf = PDF::loadView('pdf.reporte-asistencias', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('Reporte-Asistencias-' . \Carbon\Carbon::createFromFormat('n', $mes)->monthName . '-' . $anio . '.pdf');
    }
}