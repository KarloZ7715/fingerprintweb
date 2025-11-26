<?php
// -------------------------------------------
// CONFIG DB
// -------------------------------------------
$host = "mysql-supertiendasis.alwaysdata.net";
$dbname = "supertiendasis_control";
$username = "436573";
$password = "@9hUb3dgziT6hAe";

date_default_timezone_set('America/Bogota');
$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin    = $_GET['fin']    ?? date('Y-m-t');
// Filtros
$filtro_cedula = $_GET['cedula'] ?? '';
$filtro_horario = $_GET['horario'] ?? '';

// Conexión
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$fecha_inicio_obj = new DateTime($inicio);
$fecha_fin_obj = new DateTime($fin);
$dias_periodo = $fecha_inicio_obj->diff($fecha_fin_obj)->days + 1;
$fecha_hoy = date('Y-m-d');

// -------------------------------------------------
// OBTENER HORARIOS
// -------------------------------------------------
$sql_horarios = "
    SELECT DISTINCT h.id, h.nombre 
    FROM horario h
    INNER JOIN empleado e ON e.horario_id = h.id
    WHERE e.estado = 'Activo'
    ORDER BY h.nombre
";
$stmt_horarios = $pdo->query($sql_horarios);
$horarios_disponibles = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------
// CALCULAR DÍAS LABORABLES TEÓRICOS POR HORARIO (SOLO PARA REFERENCIA)
// -------------------------------------------------
$dias_laborables_por_horario = [];

$sql_dias_lab = "
SELECT
    h.id AS horario_id,
    h.nombre AS nombre_horario,
    (
        SELECT COUNT(*) 
        FROM (
            SELECT
                DATE(:inicio) + INTERVAL n DAY AS fecha,
                CASE DAYNAME(DATE(:inicio2) + INTERVAL n DAY)
                    WHEN 'Monday'    THEN 'lunes'
                    WHEN 'Tuesday'   THEN 'martes'
                    WHEN 'Wednesday' THEN 'miercoles'
                    WHEN 'Thursday'  THEN 'jueves'
                    WHEN 'Friday'    THEN 'viernes'
                    WHEN 'Saturday'  THEN 'sabado'
                    WHEN 'Sunday'    THEN 'domingo'
                END AS dia_semana
            FROM (
                SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
                UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
                UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
                UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
                UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
                UNION SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34
                UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39
                UNION SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44
                UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49
                UNION SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54
                UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59
                UNION SELECT 60 UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64
                UNION SELECT 65 UNION SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69
                UNION SELECT 70 UNION SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74
                UNION SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79
                UNION SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84
                UNION SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89
                UNION SELECT 90 UNION SELECT 91 UNION SELECT 92 UNION SELECT 93 UNION SELECT 94
                UNION SELECT 95 UNION SELECT 96 UNION SELECT 97 UNION SELECT 98 UNION SELECT 99
                UNION SELECT 100 UNION SELECT 101 UNION SELECT 102 UNION SELECT 103 UNION SELECT 104
                UNION SELECT 105 UNION SELECT 106 UNION SELECT 107 UNION SELECT 108 UNION SELECT 109
                UNION SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION SELECT 113 UNION SELECT 114
                UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION SELECT 119
                UNION SELECT 120 UNION SELECT 121 UNION SELECT 122 UNION SELECT 123 UNION SELECT 124
                UNION SELECT 125 UNION SELECT 126 UNION SELECT 127 UNION SELECT 128 UNION SELECT 129
                UNION SELECT 130 UNION SELECT 131 UNION SELECT 132 UNION SELECT 133 UNION SELECT 134
                UNION SELECT 135 UNION SELECT 136 UNION SELECT 137 UNION SELECT 138 UNION SELECT 139
                UNION SELECT 140 UNION SELECT 141 UNION SELECT 142 UNION SELECT 143 UNION SELECT 144
                UNION SELECT 145 UNION SELECT 146 UNION SELECT 147 UNION SELECT 148 UNION SELECT 149
                UNION SELECT 150 UNION SELECT 151 UNION SELECT 152 UNION SELECT 153 UNION SELECT 154
                UNION SELECT 155 UNION SELECT 156 UNION SELECT 157 UNION SELECT 158 UNION SELECT 159
                UNION SELECT 160 UNION SELECT 161 UNION SELECT 162 UNION SELECT 163 UNION SELECT 164
                UNION SELECT 165 UNION SELECT 166 UNION SELECT 167 UNION SELECT 168 UNION SELECT 169
                UNION SELECT 170 UNION SELECT 171 UNION SELECT 172 UNION SELECT 173 UNION SELECT 174
                UNION SELECT 175 UNION SELECT 176 UNION SELECT 177 UNION SELECT 178 UNION SELECT 179
                UNION SELECT 180 UNION SELECT 181 UNION SELECT 182 UNION SELECT 183 UNION SELECT 184
                UNION SELECT 185 UNION SELECT 186 UNION SELECT 187 UNION SELECT 188 UNION SELECT 189
                UNION SELECT 190 UNION SELECT 191 UNION SELECT 192 UNION SELECT 193 UNION SELECT 194
                UNION SELECT 195 UNION SELECT 196 UNION SELECT 197 UNION SELECT 198 UNION SELECT 199
                UNION SELECT 200 UNION SELECT 201 UNION SELECT 202 UNION SELECT 203 UNION SELECT 204
                UNION SELECT 205 UNION SELECT 206 UNION SELECT 207 UNION SELECT 208 UNION SELECT 209
                UNION SELECT 210 UNION SELECT 211 UNION SELECT 212 UNION SELECT 213 UNION SELECT 214
                UNION SELECT 215 UNION SELECT 216 UNION SELECT 217 UNION SELECT 218 UNION SELECT 219
                UNION SELECT 220 UNION SELECT 221 UNION SELECT 222 UNION SELECT 223 UNION SELECT 224
                UNION SELECT 225 UNION SELECT 226 UNION SELECT 227 UNION SELECT 228 UNION SELECT 229
                UNION SELECT 230 UNION SELECT 231 UNION SELECT 232 UNION SELECT 233 UNION SELECT 234
                UNION SELECT 235 UNION SELECT 236 UNION SELECT 237 UNION SELECT 238 UNION SELECT 239
                UNION SELECT 240 UNION SELECT 241 UNION SELECT 242 UNION SELECT 243 UNION SELECT 244
                UNION SELECT 245 UNION SELECT 246 UNION SELECT 247 UNION SELECT 248 UNION SELECT 249
                UNION SELECT 250 UNION SELECT 251 UNION SELECT 252 UNION SELECT 253 UNION SELECT 254
                UNION SELECT 255 UNION SELECT 256 UNION SELECT 257 UNION SELECT 258 UNION SELECT 259
                UNION SELECT 260 UNION SELECT 261 UNION SELECT 262 UNION SELECT 263 UNION SELECT 264
                UNION SELECT 265 UNION SELECT 266 UNION SELECT 267 UNION SELECT 268 UNION SELECT 269
                UNION SELECT 270 UNION SELECT 271 UNION SELECT 272 UNION SELECT 273 UNION SELECT 274
                UNION SELECT 275 UNION SELECT 276 UNION SELECT 277 UNION SELECT 278 UNION SELECT 279
                UNION SELECT 280 UNION SELECT 281 UNION SELECT 282 UNION SELECT 283 UNION SELECT 284
                UNION SELECT 285 UNION SELECT 286 UNION SELECT 287 UNION SELECT 288 UNION SELECT 289
                UNION SELECT 290 UNION SELECT 291 UNION SELECT 292 UNION SELECT 293 UNION SELECT 294
                UNION SELECT 295 UNION SELECT 296 UNION SELECT 297 UNION SELECT 298 UNION SELECT 299
                UNION SELECT 300 UNION SELECT 301 UNION SELECT 302 UNION SELECT 303 UNION SELECT 304
                UNION SELECT 305 UNION SELECT 306 UNION SELECT 307 UNION SELECT 308 UNION SELECT 309
                UNION SELECT 310 UNION SELECT 311 UNION SELECT 312 UNION SELECT 313 UNION SELECT 314
                UNION SELECT 315 UNION SELECT 316 UNION SELECT 317 UNION SELECT 318 UNION SELECT 319
                UNION SELECT 320 UNION SELECT 321 UNION SELECT 322 UNION SELECT 323 UNION SELECT 324
                UNION SELECT 325 UNION SELECT 326 UNION SELECT 327 UNION SELECT 328 UNION SELECT 329
                UNION SELECT 330 UNION SELECT 331 UNION SELECT 332 UNION SELECT 333 UNION SELECT 334
                UNION SELECT 335 UNION SELECT 336 UNION SELECT 337 UNION SELECT 338 UNION SELECT 339
                UNION SELECT 340 UNION SELECT 341 UNION SELECT 342 UNION SELECT 343 UNION SELECT 344
                UNION SELECT 345 UNION SELECT 346 UNION SELECT 347 UNION SELECT 348 UNION SELECT 349
                UNION SELECT 350 UNION SELECT 351 UNION SELECT 352 UNION SELECT 353 UNION SELECT 354
                UNION SELECT 355 UNION SELECT 356 UNION SELECT 357 UNION SELECT 358 UNION SELECT 359
                UNION SELECT 360 UNION SELECT 361 UNION SELECT 362 UNION SELECT 363 UNION SELECT 364
                UNION SELECT 365
            ) d
            WHERE DATE(:inicio3) + INTERVAL n DAY <= :fin
        ) fechas
        WHERE 
            JSON_UNQUOTE(JSON_EXTRACT(h.dias_laborables, CONCAT('$.', dia_semana))) = 'true'
            OR (dia_semana = 'miercoles' AND JSON_UNQUOTE(JSON_EXTRACT(h.dias_laborables, '$.miércoles')) = 'true')
            OR (dia_semana = 'sabado' AND JSON_UNQUOTE(JSON_EXTRACT(h.dias_laborables, '$.sábado')) = 'true')
    ) AS dias_laborables_periodo
FROM horario h
WHERE h.activo = 1
";

$stmt_lab = $pdo->prepare($sql_dias_lab);
$stmt_lab->execute([
    ':inicio' => $inicio,
    ':inicio2' => $inicio,
    ':inicio3' => $inicio,
    ':fin' => $fin
]);

foreach ($stmt_lab->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dias_laborables_por_horario[$row['horario_id']] = (int)$row['dias_laborables_periodo'];
}

// -------------------------------------------------
// CONTAR DÍAS CON ENTRADA REALES POR HORARIO (LA TIENDA ABRIÓ)
// -------------------------------------------------
$dias_con_entrada_por_horario = [];

$sql_entrada = "
    SELECT
        horario_id,
        COUNT(DISTINCT fecha) AS dias_con_entrada
    FROM asistencia_diaria
    WHERE fecha BETWEEN :inicio AND :fin
      AND hora_entrada IS NOT NULL
    GROUP BY horario_id
";

$stmt_entrada = $pdo->prepare($sql_entrada);
$stmt_entrada->execute([':inicio' => $inicio, ':fin' => $fin]);

foreach ($stmt_entrada->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dias_con_entrada_por_horario[$row['horario_id']] = (int)$row['dias_con_entrada'];
}

// -------------------------------------------------
// CONSULTA PRINCIPAL
// -------------------------------------------------
$sql = "
SELECT 
    e.id,
    e.cedula,
    e.primer_nombre,
    e.segundo_nombre,
    e.primer_apellido,
    e.segundo_apellido,
    h.id AS horario_id,
    h.nombre AS horario_nombre,
    h.hora_entrada AS horario_entrada,
    h.hora_salida AS horario_salida,
    h.dias_laborables
FROM empleado e
INNER JOIN horario h ON h.id = e.horario_id
WHERE e.estado = 'Activo'
  AND e.horario_id IS NOT NULL
";

$params = [];

if (!empty($filtro_cedula)) {
    $sql .= " AND e.cedula LIKE :cedula";
    $params[":cedula"] = "%$filtro_cedula%";
}

if (!empty($filtro_horario)) {
    $sql .= " AND h.id = :horario_id";
    $params[":horario_id"] = $filtro_horario;
}

$sql .= " ORDER BY h.nombre ASC, e.primer_nombre, e.primer_apellido";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------
// CALCULAR MÉTRICAS PARA CADA EMPLEADO
// -------------------------------------------------
$rows = [];

foreach ($empleados as $emp) {
    $empleado_id = $emp['id'];
    $horario_id = $emp['horario_id'];
    
    // 1. DÍAS LABORABLES SEMANA
    $dias_json = json_decode($emp['dias_laborables'], true);
    $dias_laborables_semana = 0;
    if ($dias_json) {
        foreach ($dias_json as $dia => $valor) {
            if ($valor === true) $dias_laborables_semana++;
        }
    }
    
    // 2. DÍAS QUE DEBIÓ TRABAJAR = Días donde AL MENOS alguien del horario marcó entrada
    $dias_debia_trabajar = $dias_con_entrada_por_horario[$horario_id] ?? 0;
    
    // 3. DÍAS ASISTIDOS (con entrada registrada por este empleado)
    $sql_asistidos = "
        SELECT COUNT(DISTINCT fecha) as total
        FROM asistencia_diaria
        WHERE empleado_id = :empleado_id
          AND fecha BETWEEN :inicio AND :fin
          AND hora_entrada IS NOT NULL
    ";
    $stmt_asist = $pdo->prepare($sql_asistidos);
    $stmt_asist->execute([
        ':empleado_id' => $empleado_id,
        ':inicio' => $inicio,
        ':fin' => $fin
    ]);
    $dias_asistidos = (int)$stmt_asist->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 4. DÍAS SIN SALIDA (entrada pero sin salida, excluyendo hoy)
    $sql_sin_salida = "
        SELECT COUNT(DISTINCT fecha) as total,
               GROUP_CONCAT(DISTINCT DATE_FORMAT(fecha, '%d/%m/%Y') ORDER BY fecha SEPARATOR ', ') as fechas
        FROM asistencia_diaria
        WHERE empleado_id = :empleado_id
          AND fecha BETWEEN :inicio AND :fin
          AND fecha < :fecha_hoy
          AND hora_entrada IS NOT NULL
          AND (hora_salida IS NULL OR hora_salida = '')
    ";
    $stmt_sin_sal = $pdo->prepare($sql_sin_salida);
    $stmt_sin_sal->execute([
        ':empleado_id' => $empleado_id,
        ':inicio' => $inicio,
        ':fin' => $fin,
        ':fecha_hoy' => $fecha_hoy
    ]);
    $result_sin_sal = $stmt_sin_sal->fetch(PDO::FETCH_ASSOC);
    $dias_sin_salida = (int)$result_sin_sal['total'];
    $fechas_sin_salida = $result_sin_sal['fechas'] ?? '';
    
    // 5. JUSTIFICACIONES APROBADAS CON FECHAS
    $sql_justif = "
        SELECT 
            COUNT(*) as cantidad,
            SUM(COALESCE(DATEDIFF(fecha_expiracion, fecha_incapacidad) + 1, 0)) as dias_cubiertos,
            GROUP_CONCAT(
                CONCAT(DATE_FORMAT(fecha_incapacidad, '%d/%m/%Y'), ' - ', DATE_FORMAT(fecha_expiracion, '%d/%m/%Y'))
                ORDER BY fecha_incapacidad 
                SEPARATOR '; '
            ) as fechas_justificaciones
        FROM justificacion
        WHERE empleado_id = :empleado_id
          AND estado = 'aprobada'
          AND fecha_expiracion >= :inicio
          AND fecha_incapacidad <= :fin
    ";
    $stmt_justif = $pdo->prepare($sql_justif);
    $stmt_justif->execute([
        ':empleado_id' => $empleado_id,
        ':inicio' => $inicio,
        ':fin' => $fin
    ]);
    $result_justif = $stmt_justif->fetch(PDO::FETCH_ASSOC);
    $justificaciones_aprobadas = (int)$result_justif['cantidad'];
    $dias_cubiertos = (int)$result_justif['dias_cubiertos'];
    $fechas_justificaciones = $result_justif['fechas_justificaciones'] ?? '';
    
    // 6. CALCULAR FALTAS REALES
    $sql_faltas = "
        SELECT fechas_con_actividad.fecha, DATE_FORMAT(fechas_con_actividad.fecha, '%d/%m/%Y') as fecha_fmt
        FROM (
            SELECT DISTINCT fecha
            FROM asistencia_diaria
            WHERE horario_id = :horario_id
              AND fecha BETWEEN :inicio AND :fin
              AND hora_entrada IS NOT NULL
        ) fechas_con_actividad
        WHERE NOT EXISTS (
            SELECT 1 FROM asistencia_diaria ad
            WHERE ad.empleado_id = :empleado_id
              AND ad.fecha = fechas_con_actividad.fecha
              AND ad.hora_entrada IS NOT NULL
        )
        AND NOT EXISTS (
            SELECT 1 FROM justificacion j
            WHERE j.empleado_id = :empleado_id2
              AND j.estado = 'aprobada'
              AND fechas_con_actividad.fecha BETWEEN j.fecha_incapacidad AND j.fecha_expiracion
        )
        ORDER BY fechas_con_actividad.fecha
    ";
    
    $stmt_faltas = $pdo->prepare($sql_faltas);
    $stmt_faltas->execute([
        ':horario_id' => $horario_id,
        ':inicio' => $inicio,
        ':fin' => $fin,
        ':empleado_id' => $empleado_id,
        ':empleado_id2' => $empleado_id
    ]);
    
    $faltas_array = $stmt_faltas->fetchAll(PDO::FETCH_COLUMN, 1);
    $faltas_reales = count($faltas_array);
    $fechas_faltas = implode(', ', $faltas_array);
    
    // 7. PORCENTAJES (BASADOS EN DÍAS REALES CON ACTIVIDAD)
    $porcentaje_asistencia = $dias_debia_trabajar > 0 ? ($dias_asistidos / $dias_debia_trabajar) * 100 : 100;
    $porcentaje_faltas = $dias_debia_trabajar > 0 ? ($faltas_reales / $dias_debia_trabajar) * 100 : 0;
    
    $rows[] = array_merge($emp, [
        'dias_laborables_semana' => $dias_laborables_semana,
        'dias_debia_trabajar' => $dias_debia_trabajar,
        'dias_asistidos' => $dias_asistidos,
        'faltas_reales' => $faltas_reales,
        'justificaciones_aprobadas' => $justificaciones_aprobadas,
        'dias_cubiertos' => $dias_cubiertos,
        'dias_sin_salida' => $dias_sin_salida,
        'fechas_sin_salida' => $fechas_sin_salida,
        'fechas_faltas' => $fechas_faltas,
        'fechas_justificaciones' => $fechas_justificaciones,
        'porcentaje_asistencia' => round($porcentaje_asistencia, 1),
        'porcentaje_faltas' => round($porcentaje_faltas, 1)
    ]);
}

// Agrupar por horario
$datos_por_horario = [];
foreach ($rows as $row) {
    $horario_id = $row['horario_id'];
    if (!isset($datos_por_horario[$horario_id])) {
        $datos_por_horario[$horario_id] = [
            'horario_nombre' => $row['horario_nombre'],
            'horario_entrada' => $row['horario_entrada'],
            'horario_salida' => $row['horario_salida'],
            'dias_laborables_teoricos' => $dias_laborables_por_horario[$horario_id] ?? 0,
            'dias_con_entrada_reales' => $dias_con_entrada_por_horario[$horario_id] ?? 0,
            'empleados' => []
        ];
    }
    $datos_por_horario[$horario_id]['empleados'][] = $row;
}

// CARDS GLOBALES
$total_empleados = count($rows);
$total_asistencias = array_sum(array_column($rows, 'dias_asistidos'));
$total_faltas = array_sum(array_column($rows, 'faltas_reales'));
$total_debia_trabajar = 0;
$horarios_procesados = [];
foreach ($rows as $row) {
    if (!in_array($row['horario_id'], $horarios_procesados)) {
        $total_debia_trabajar += $dias_con_entrada_por_horario[$row['horario_id']] ?? 0;
        $horarios_procesados[] = $row['horario_id'];
    }
}
$total_sin_salida = array_sum(array_column($rows, 'dias_sin_salida'));

$total_oportunidades_asistencia = 0;
foreach ($rows as $row) {
    $total_oportunidades_asistencia += $row['dias_debia_trabajar'];
}
$efectividad_global = $total_oportunidades_asistencia > 0 ? ($total_asistencias / $total_oportunidades_asistencia) * 100 : 100;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencias</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fa;
            color: #2d3748;
            line-height: 1.6;
        }
        
        /* ========== LOADER ========== */
        #loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 249, 250, 0.98);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            backdrop-filter: blur(3px);
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #e2e8f0;
            border-top: 5px solid #4299e1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loader-text {
            margin-top: 25px;
            color: #4a5568;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .loader-subtext {
            margin-top: 8px;
            color: #a0aec0;
            font-size: 14px;
        }
        
        /* ========== RESTO DEL CSS ========== */
        .header {
            background: white;
            padding: 25px 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-title h1 {
            font-size: 24px;
            color: #1a202c;
            font-weight: 600;
        }
        
        .date-filter {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .date-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .date-group label {
            font-size: 11px;
            font-weight: 500;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .date-group input, .date-group select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            transition: all 0.2s;
        }
        
        .date-group input:focus, .date-group select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        
        .btn-filter {
            background: #4299e1;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            margin-top: auto;
        }
        
        .btn-filter:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .period-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .date-badge {
            background: #edf2f7;
            color: #2d3748;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transform: translateY(-2px);
        }
        
        .card-label {
            font-size: 12px;
            color: #718096;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .card-value {
            font-size: 32px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .card-footer {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f7fafc;
            font-size: 13px;
            color: #a0aec0;
        }
        
        .horario-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .horario-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f7fafc;
        }
        
        .horario-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .horario-time {
            color: #718096;
            font-size: 14px;
        }
        
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #718096;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
        }
        
        .btn-clear {
            background: #edf2f7;
            color: #4a5568;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-clear:hover {
            background: #e2e8f0;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
        }
        
        .stat-card {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-label {
            font-size: 11px;
            color: #718096;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
        }
        
        /* ========== TABLA RESPONSIVE ========== */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -25px;
            padding: 0 25px;
        }
        
        table { 
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 1100px;
        }
        
        thead {
            background: #f7fafc;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #f7fafc;
            color: #2d3748;
        }
        
        tbody tr:hover {
            background: #f7fafc;
        }
        
        .employee-name {
            font-weight: 500;
            color: #2d3748;
            white-space: nowrap;
        }
        
        .employee-cedula {
            color: #a0aec0;
            font-size: 11px;
            display: block;
            margin-top: 2px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }
        
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #feebc8; color: #744210; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .badge-info { background: #bee3f8; color: #2c5282; }
        
        .btn-details {
            background: #edf2f7;
            color: #4a5568;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .btn-details:hover {
            background: #e2e8f0;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
        }
        
        .modal-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #a0aec0;
        }
        
        .modal-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 6px;
        }
        
        .modal-section-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .modal-section-content {
            color: #4a5568;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .info-note {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .info-note h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .info-note ul {
            list-style: none;
            padding: 0;
        }
        
        .info-note li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #4a5568;
            font-size: 14px;
        }
        
        .info-note li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #4299e1;
            font-weight: bold;
            font-size: 18px;
        }
        
        .footer {
            text-align: center;
            padding: 30px 20px;
            color: #a0aec0;
            font-size: 14px;
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer strong {
            color: #4a5568;
        }
        
        /* ========== RESPONSIVE ========== */
        @media print {
            #loader-overlay {
                display: none !important;
            }
            .header, .filters-bar, .btn-filter, .btn-clear, .btn-details {
                display: none !important;
            }
            body {
                background: white;
            }
            .horario-section {
                page-break-inside: avoid;
            }
        }
        
        @media (max-width: 1024px) {
            .cards-container {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-value {
                font-size: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }
            
            .header-content {
                flex-direction: column;
            }
            
            .header-title h1 {
                font-size: 20px;
            }
            
            .date-filter {
                flex-direction: column;
                width: 100%;
            }
            
            .date-group {
                width: 100%;
            }
            
            .date-group input {
                width: 100%;
            }
            
            .cards-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .card {
                padding: 15px;
            }
            
            .card-value {
                font-size: 24px;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
                padding: 15px;
                gap: 10px;
            }
            
            .horario-section {
                padding: 15px;
            }
            
            .table-wrapper {
                margin: 0 -15px;
                padding: 0 15px;
            }
            
            .period-info {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            
            .cards-container {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 20px;
                width: 95%;
            }
        }
    </style>
</head>
<body>

<!-- LOADER -->
<div id="loader-overlay">
    <div class="spinner"></div>
    <div class="loader-text">📊 Cargando reporte...</div>
    <div class="loader-subtext">Procesando datos de asistencia</div>
</div>

<div class="header">
    <div class="header-content">
        <div class="header-title">
            <h1>📊 Reporte de Asistencias</h1>
        </div>
        
        <form method="GET" class="date-filter">
            <div class="date-group">
                <label>Fecha Inicio</label>
                <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>" required>
            </div>
            <div class="date-group">
                <label>Fecha Fin</label>
                <input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>" required>
            </div>
            <button type="submit" class="btn-filter">Consultar</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="period-info">
        <span class="date-badge">
            📅 <?= date('d/m/Y', strtotime($inicio)) ?> - <?= date('d/m/Y', strtotime($fin)) ?>
        </span>
        <span style="color: #718096; font-weight: 500;">
            <?= $dias_periodo ?> días en el período
        </span>
    </div>

    <!-- CARDS GLOBALES -->
    <div class="cards-container">
        <div class="card">
            <div class="card-label">Total Empleados</div>
            <div class="card-value"><?= $total_empleados ?></div>
            <div class="card-footer">Empleados activos</div>
        </div>
        
        <div class="card">
            <div class="card-label">Días que debieron trabajar</div>
            <div class="card-value"><?= $total_debia_trabajar ?></div>
            <div class="card-footer">Días con actividad real</div>
        </div>
        
        <div class="card">
            <div class="card-label">Asistencias Totales</div>
            <div class="card-value"><?= $total_asistencias ?></div>
            <div class="card-footer">Días asistidos</div>
        </div>
        
        <div class="card">
            <div class="card-label">Faltas Totales</div>
            <div class="card-value"><?= $total_faltas ?></div>
            <div class="card-footer">Días sin asistir</div>
        </div>
        
        <div class="card">
            <div class="card-label">Efectividad Global</div>
            <div class="card-value"><?= number_format($efectividad_global, 1) ?>%</div>
            <div class="card-footer">Cumplimiento general</div>
        </div>
        
        <div class="card">
            <div class="card-label">Sin Marcar Salida</div>
            <div class="card-value"><?= $total_sin_salida ?></div>
            <div class="card-footer">Pendientes</div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-bar">
        <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex: 1; flex-wrap: wrap;">
            <input type="hidden" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
            <input type="hidden" name="fin" value="<?= htmlspecialchars($fin) ?>">
            
            <div class="filter-group">
                <label>Buscar por Cédula</label>
                <input type="text" name="cedula" placeholder="Ingrese cédula..." value="<?= htmlspecialchars($filtro_cedula) ?>">
            </div>
            
            <div class="filter-group">
                <label>Filtrar por Horario</label>
                <select name="horario">
                    <option value="">Todos los horarios</option>
                    <?php foreach ($horarios_disponibles as $h): ?>
                        <option value="<?= $h['id'] ?>" <?= $filtro_horario == $h['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($h['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Filtrar</button>
            <a href="?inicio=<?= $inicio ?>&fin=<?= $fin ?>" class="btn-clear">Limpiar</a>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="font-size: 48px; color: #e2e8f0; margin-bottom: 20px;">📭</div>
            <div style="color: #a0aec0; font-size: 16px;">No hay datos para mostrar</div>
        </div>
    <?php else: ?>
        <?php foreach ($datos_por_horario as $horario_id => $datos_horario): ?>
            <?php
            $empleados_horario = $datos_horario['empleados'];
            $total_asist_horario = array_sum(array_column($empleados_horario, 'dias_asistidos'));
            $total_faltas_horario = array_sum(array_column($empleados_horario, 'faltas_reales'));
            $total_debia_horario = $datos_horario['dias_con_entrada_reales'];
            $total_sin_salida_horario = array_sum(array_column($empleados_horario, 'dias_sin_salida'));
            ?>
            
            <div class="horario-section">
                <div class="horario-header">
                    <div>
                        <div class="horario-title"><?= htmlspecialchars($datos_horario['horario_nombre']) ?></div>
                        <div class="horario-time">
                            <?= date('g:i A', strtotime($datos_horario['horario_entrada'])) ?> - 
                            <?= date('g:i A', strtotime($datos_horario['horario_salida'])) ?>
                        </div>
                    </div>
                    <div style="text-align: right; color: #718096;">
                        <strong><?= count($empleados_horario) ?></strong> empleados
                    </div>
                </div>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-label">Debieron Trabajar</div>
                        <div class="stat-value"><?= $total_debia_horario ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Asistencias</div>
                        <div class="stat-value"><?= $total_asist_horario ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Faltas</div>
                        <div class="stat-value"><?= $total_faltas_horario ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Sin Salida</div>
                        <div class="stat-value"><?= $total_sin_salida_horario ?></div>
                    </div>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Días/Sem</th>
                                <th>Debió Trabajar</th>
                                <th>Asistidos</th>
                                <th>Faltas</th>
                                <th>% Asist.</th>
                                <th>% Faltas</th>
                                <th>Justif.</th>
                                <th>Cubiertos</th>
                                <th>Sin Salida</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados_horario as $r): ?>
                            <tr>
                                <td>
                                    <div class="employee-name">
                                        <?= htmlspecialchars($r['primer_nombre']) ?> 
                                        <?= htmlspecialchars($r['segundo_nombre']) ?> 
                                        <?= htmlspecialchars($r['primer_apellido']) ?> 
                                        <?= htmlspecialchars($r['segundo_apellido']) ?>
                                    </div>
                                    <span class="employee-cedula">CC: <?= htmlspecialchars($r['cedula']) ?></span>
                                </td>
                                <td><?= $r['dias_laborables_semana'] ?></td>
                                <td><strong><?= $r['dias_debia_trabajar'] ?></strong></td>
                                <td><strong style="color: #38a169;"><?= $r['dias_asistidos'] ?></strong></td>
                                <td><strong style="color: #e53e3e;"><?= $r['faltas_reales'] ?></strong></td>
                                <td>
                                    <?php 
                                    $pct_asist = floatval($r['porcentaje_asistencia']);
                                    $badge_class = $pct_asist >= 90 ? 'badge-success' : ($pct_asist >= 70 ? 'badge-info' : ($pct_asist >= 50 ? 'badge-warning' : 'badge-danger'));
                                    ?>
                                    <span class="badge <?= $badge_class ?>">
                                        <?= number_format($pct_asist, 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $pct_falta = floatval($r['porcentaje_faltas']);
                                    $badge_class = $pct_falta < 10 ? 'badge-success' : ($pct_falta < 30 ? 'badge-warning' : 'badge-danger');
                                    ?>
                                    <span class="badge <?= $badge_class ?>">
                                        <?= number_format($pct_falta, 1) ?>%
                                    </span>
                                </td>
                                <td><?= $r['justificaciones_aprobadas'] ?></td>
                                <td><?= $r['dias_cubiertos'] ?></td>
                                <td>
                                    <?php if ($r['dias_sin_salida'] > 0): ?>
                                        <span class="badge badge-warning"><?= $r['dias_sin_salida'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['fechas_sin_salida']) || !empty($r['fechas_faltas']) || !empty($r['fechas_justificaciones'])): ?>
                                        <button class="btn-details" onclick="showModal(<?= htmlspecialchars(json_encode($r)) ?>)">
                                            Ver fechas
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="info-note">
        <h3>ℹ️ Información del Reporte</h3>
        <ul>
            <li><strong>Días/Sem:</strong> Días que trabaja por semana según horario.</li>
            <li><strong>Debió Trabajar:</strong> Días donde AL MENOS un empleado del horario marcó entrada (la tienda abrió).</li>
            <li><strong>Asistidos:</strong> Días con entrada registrada por el empleado.</li>
            <li><strong>Faltas:</strong> Días donde hubo actividad en el horario, pero el empleado NO asistió y NO tiene justificación.</li>
            <li><strong>Sin Salida:</strong> Días (antes de hoy) con entrada pero sin salida.</li>
            <li><strong>% Asistencia:</strong> (Asistidos / Debió Trabajar Real) × 100</li>
            <li><strong>% Faltas:</strong> (Faltas / Debió Trabajar Real) × 100</li>
            <li><strong>Efectividad Global:</strong> (Total Asistencias / Total Oportunidades) × 100</li>
        </ul>
    </div>
</div>

<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header" id="modalHeader"></div>
        <div id="modalBody"></div>
    </div>
</div>

<div class="footer">
    Powered by <strong>ElectroCode</strong> © <?= date('Y') ?>
</div>

<script>
// Ocultar loader cuando la página termine de cargar
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loader-overlay').style.opacity = '0';
        setTimeout(function() {
            document.getElementById('loader-overlay').style.display = 'none';
        }, 300);
    }, 500);
});

function showModal(data) {
    const modal = document.getElementById('detailsModal');
    const header = document.getElementById('modalHeader');
    const body = document.getElementById('modalBody');
    
    header.textContent = data.primer_nombre + ' ' + data.primer_apellido;
    
    let html = '';
    
    if (data.fechas_sin_salida) {
        html += '<div class="modal-section">';
        html += '<div class="modal-section-title">🚨 Días sin marcar salida:</div>';
        html += '<div class="modal-section-content">' + data.fechas_sin_salida + '</div>';
        html += '</div>';
    }
    
    if (data.fechas_faltas) {
        html += '<div class="modal-section">';
        html += '<div class="modal-section-title">❌ Días de faltas:</div>';
        html += '<div class="modal-section-content">' + data.fechas_faltas + '</div>';
        html += '</div>';
    }
    
    if (data.fechas_justificaciones) {
        html += '<div class="modal-section">';
        html += '<div class="modal-section-title">✅ Justificaciones aprobadas:</div>';
        html += '<div class="modal-section-content">' + data.fechas_justificaciones.replace(/; /g, '<br>') + '</div>';
        html += '</div>';
    }
    
    if (!data.fechas_sin_salida && !data.fechas_faltas && !data.fechas_justificaciones) {
        html = '<div style="text-align: center; padding: 30px; color: #a0aec0;">Sin registros para mostrar</div>';
    }
    
    body.innerHTML = html;
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html> 