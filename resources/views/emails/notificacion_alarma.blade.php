<!DOCTYPE html>
<html>
<head>
    <title>Notificación de Alarma</title>
</head>
<body>
    <h2>{{ $mensaje }}</h2>
    <p>Alarma: {{ $alarma->nombre }}</p>
    <p>Estado: {{ $alarma->estado }}</p>
    <p>Evento: {{ $evento->Evento }} - {{ $evento->Accion }}</p>
    <p>Fecha: {{ $evento->fecha_evento }}</p>
</body>
</html>