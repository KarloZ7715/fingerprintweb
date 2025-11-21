<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportePDFController extends Controller
{
    public function generarPDF($mes, $anio)
    {
        $reporteAsistencias = DB::select("
            SELECT 
                e.id,
                e.cedula,
                CONCAT(e.primer_nombre, ' ', e.primer_apellido) as empleado,
                COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') THEN ad.fecha END) as asistencias,
                (
                    SELECT COUNT(DISTINCT fecha)
                    FROM asistencia_diaria
                    WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                        AND horario_id = e.horario_id
                        AND estado IN ('completo', 'incompleto')
                ) as dias_laborados,
                (
                    (
                        SELECT COUNT(DISTINCT fecha)
                        FROM asistencia_diaria
                        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                            AND horario_id = e.horario_id
                            AND estado IN ('completo', 'incompleto')
                    ) - COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') THEN ad.fecha END)
                ) as ausencias,
                CASE 
                    WHEN (
                        SELECT COUNT(DISTINCT fecha)
                        FROM asistencia_diaria
                        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                            AND horario_id = e.horario_id
                            AND estado IN ('completo', 'incompleto')
                    ) = 0 THEN 0
                    ELSE ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
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
                    ) = 0 THEN 'CRÍTICO'
                    WHEN ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
                        )
                    , 1) >= 95 THEN 'EXCELENTE'
                    WHEN ROUND(
                        (COUNT(DISTINCT CASE WHEN ad.estado IN ('completo', 'incompleto') THEN ad.fecha END) * 100.0) 
                        / (
                            SELECT COUNT(DISTINCT fecha)
                            FROM asistencia_diaria
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                AND horario_id = e.horario_id
                                AND estado IN ('completo', 'incompleto')
                        )
                    , 1) >= 90 THEN 'ALERTA'
                    ELSE 'CRÍTICO'
                END as estado
            FROM empleado e
            LEFT JOIN asistencia_diaria ad ON e.id = ad.empleado_id 
                AND MONTH(ad.fecha) = ? AND YEAR(ad.fecha) = ?
            WHERE e.estado = 'Activo'
            GROUP BY e.id, e.cedula, e.primer_nombre, e.primer_apellido, e.horario_id
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
                AND e.estado = 'Activo'
            GROUP BY e.id, e.cedula, e.primer_nombre, e.primer_apellido
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