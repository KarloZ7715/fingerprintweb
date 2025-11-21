<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            color: #333;
            background: white;
        }
        
        /* HEADER */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10px;
        }
        .header h1 { 
            font-size: 18px; 
            font-weight: bold; 
            color: #1e40af;
            margin-bottom: 5px;
        }
        .header .info {
            font-size: 9px;
            color: #666;
        }
        
        /* DESCRIPCIÓN */
        .descripcion {
            text-align: justify;
            font-size: 9px;
            color: #555;
            margin-bottom: 15px;
            line-height: 1.4;
            background: #f9fafb;
            padding: 10px;
            border-left: 4px solid #1e40af;
        }
        
        /* TABLA */
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px;
            background: white;
        }
        
        .table thead { 
            background-color: #1e40af; 
            color: white;
        }
        
        .table th { 
            padding: 10px 8px; 
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e40af;
            font-size: 9px;
        }
        
        .table td { 
            padding: 8px; 
            border: 1px solid #ddd;
            font-size: 9px;
        }
        
        .table tbody tr:nth-child(even) { 
            background-color: #f9fafb; 
        }
        
        /* BADGES DE ESTADO */
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 12px; 
            font-weight: bold; 
            color: white;
            text-align: center;
            font-size: 8px;
        }
        .badge-excelente { 
            background-color: #22c55e; 
        }
        .badge-alerta { 
            background-color: #f59e0b; 
        }
        .badge-crítico { 
            background-color: #ef4444; 
        }
        
        /* TÍTULOS DE SECCIÓN */
        .section-title { 
            font-size: 11px; 
            font-weight: bold; 
            margin-top: 20px; 
            margin-bottom: 10px; 
            color: white;
            background-color: #1e40af;
            padding: 8px 10px;
            border-radius: 4px;
        }
        
        /* FOOTER */
        .footer { 
            margin-top: 25px; 
            padding: 10px; 
            background-color: #f3f4f6; 
            border-radius: 4px; 
            border-left: 4px solid #1e40af;
            font-size: 8px; 
            color: #555;
            line-height: 1.5;
            text-align: justify;
        }
        
        /* UTILIDADES */
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .text-green { color: #22c55e; }
        .text-yellow { color: #f59e0b; }
        .text-red { color: #ef4444; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <h1>📊 REPORTE DE ASISTENCIAS, FALTAS Y RETRASOS</h1>
        <div class="info">
            <strong>Período:</strong> {{ \Carbon\Carbon::createFromFormat('n', $mes)->monthName }} de {{ $anio }}<br>
            <strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- DESCRIPCIÓN -->
    <div class="descripcion">
        Este informe resume la asistencia, ausencias, desempeño y retrasos del personal durante el período evaluado. 
        Está diseñado para que el dueño de la tienda tenga una visión clara, informativa y accionable del comportamiento laboral. 
        Los datos permiten identificar empleados con desempeño destacado y aquellos que requieren seguimiento.
    </div>

    <!-- TABLA 1: ASISTENCIAS -->
    <div class="section-title">📋 Tabla de Asistencias por Empleado</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Cédula</th>
                <th style="width: 28%;">Empleado</th>
                <th style="width: 12%; text-align: center;">Asistencias</th>
                <th style="width: 12%; text-align: center;">Ausencias</th>
                <th style="width: 12%; text-align: center;">Tasa (%)</th>
                <th style="width: 24%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporteAsistencias as $fila)
                <tr>
                    <td class="bold">{{ $fila->cedula }}</td>
                    <td>{{ $fila->empleado }}</td>
                    <td class="center bold">{{ $fila->asistencias ?? 0 }}</td>
                    <td class="center bold">{{ $fila->ausencias ?? 0 }}</td>
                    <td class="center bold {{ ($fila->tasa ?? 0) >= 95 ? 'text-green' : (($fila->tasa ?? 0) >= 90 ? 'text-yellow' : 'text-red') }}">
                        {{ number_format($fila->tasa ?? 0, 1) }}%
                    </td>
                    <td class="center">
                        <span class="badge badge-{{ strtolower($fila->estado) }}">
                            {{ strtoupper($fila->estado) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">No hay datos disponibles para el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TABLA 2: RETRASOS -->
    <div class="section-title">⏰ Tabla Detallada de Retrasos</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Cédula</th>
                <th style="width: 35%;">Empleado</th>
                <th style="width: 15%; text-align: center;">Cantidad</th>
                <th style="width: 17%; text-align: center;">Promedio (min)</th>
                <th style="width: 18%; text-align: center;">Máximo (min)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporteRetrasos as $fila)
                <tr>
                    <td class="bold">{{ $fila->cedula }}</td>
                    <td>{{ $fila->empleado }}</td>
                    <td class="center bold">{{ $fila->cantidad }}</td>
                    <td class="center bold text-yellow">{{ number_format($fila->promedio, 1) }}</td>
                    <td class="center bold text-red">{{ $fila->maximo }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">No hay retrasos registrados en este período</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <strong>📌 Información Importante:</strong> Este reporte es confidencial y solo debe ser utilizado con fines de control laboral. 
        Los datos mostrados facilitan la toma de decisiones y mejora del control laboral. Para consultas o aclaraciones, 
        contactar al departamento de Recursos Humanos.
    </div>
</body>
</html>