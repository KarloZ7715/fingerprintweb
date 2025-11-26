<div style="padding: 1.5rem;">
    <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1rem;">Detalles de la justificación</h2>
    <div style="font-size: 1rem; line-height: 2; letter-spacing: 0.02em;">
        <strong>Fecha de registro:</strong> {{ $record->created_at }}<br>
        <strong>Nombre empleado:</strong> {{ $record->empleado->primer_nombre }} {{ $record->empleado->primer_apellido }}<br>
        <strong>Cédula:</strong> {{ $record->empleado->cedula }}<br>
        <strong>Tipo:</strong> {{ $record->tipo }}<br>
        <strong>Estado:</strong> {{ $record->estado }}<br>
        <strong>Motivo:</strong> {{ $record->motivo ?? '(Sin motivo)' }}<br>
        <strong>Aprobado por:</strong> {{ optional($record->administrador)->primer_nombre ?? '(N/A)' }}<br>
        <strong>Fecha de aprobación:</strong> {{ $record->fecha_aprobacion ?? '(N/A)' }}<br>
        @if($record->fecha_incapacidad)
            <strong>Fecha de inicio de novedad:</strong> {{ \Carbon\Carbon::parse($record->fecha_incapacidad)->format('Y-m-d') }}<br>
        @endif
        @if($record->plazo_dias)
            <strong>Duración (días):</strong> {{ $record->plazo_dias }}<br>
        @endif
        @if($record->fecha_expiracion)
            <strong>Fecha de expiración de novedad:</strong> {{ \Carbon\Carbon::parse($record->fecha_expiracion)->format('Y-m-d') }}<br>
        @endif
    </div>
</div>