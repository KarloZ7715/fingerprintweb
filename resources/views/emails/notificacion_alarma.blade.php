<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notificación de Alarma</title>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .card {
            background: #fff;
            max-width: 420px;
            margin: 40px auto;
            box-shadow: 0 2px 12px rgba(44,62,80,.16);
            border-radius: 15px;
            overflow: hidden;
            color: #222;
            border-left: 7px solid #FD3A55;
        }
        .header {
            background: linear-gradient(90deg, #fd3a55, #ffb067);
            color: #fff;
            text-align: center;
            padding: 24px 0 14px 0;
        }
        .header i {
            font-size: 42px;
        }
        .content {
            padding: 30px 24px;
        }
        h2 {
            margin: 10px 0 20px 0;
            font-weight: 700;
            font-size: 1.3em;
            color: #fd3a55;
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
            margin: 20px 0 0 0;
        }
        ul li {
            margin: 10px 0;
            font-size: 1.09em;
            color: #444;
        }
        .label {
            font-weight: 600;
            color: #715CFE;
        }
        .footer {
            background: #f6f6f6;
            text-align: center;
            padding: 17px;
            font-size: 0.90em;
            color: #888;
        }
        .icon-alarm {
            font-size: 32px;
            vertical-align: middle;
            color: #FD3A55;
        }
        @media only screen and (max-width: 480px) {
            .card { max-width: 99vw; margin: 5vw 2vw; }
            .content { padding: 18px 9px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="icon-alarm">🔔</span><br>
            <b>Alarma de Seguridad</b>
        </div>
        <div class="content">
            <h2>{{ $mensaje }}</h2>
            <ul>
                <li><span class="label">Alarma:</span> {{ $alarma->nombre }}</li>
                <li><span class="label">Estado actual:</span> {{ $alarma->estado }}</li>
                <li><span class="label">Evento:</span> {{ $evento->Evento }} <span style="color:#FD3A55">•</span> {{ $evento->Accion }}</li>
                <li><span class="label">Fecha:</span> {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y H:i:s') }}</li>
            </ul>
        </div>
        <div class="footer">
            Este correo fue generado automáticamente por el sistema de alarmas de ElectroCode.<br>
            <span style="font-size:1.2em;color:#fd3a55;">💻</span>
        </div>
    </div>
</body>
</html>