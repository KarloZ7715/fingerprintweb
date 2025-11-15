<div class="space-y-4">
    {{-- Encabezado con estado --}}
    <div class="flex items-center justify-between p-4 rounded-lg {{ 
        $asistencia->estado === 'Puntual' ? 'bg-green-50 border-l-4 border-green-500' : 
        ($asistencia->estado === 'Tarde' ? 'bg-yellow-50 border-l-4 border-yellow-500' : 'bg-red-50 border-l-4 border-red-500') 
    }}">
        <div>
            <h3 class="text-xl font-bold {{ 
                $asistencia->estado === 'Puntual' ? 'text-green-700' : 
                ($asistencia->estado === 'Tarde' ? 'text-yellow-700' : 'text-red-700') 
            }}">
                Estado: {{ $asistencia->estado }}
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                {{ \Carbon\Carbon::parse($asistencia->fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ 
                $asistencia->metodo_registro === 'Huella' ? 'bg-green-100 text-green-800' : 
                ($asistencia->metodo_registro === 'Manual' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') 
            }}">
                {{ $asistencia->metodo_registro }}
            </span>
            <p class="text-xs text-gray-500 mt-1">Método de registro</p>
        </div>
    </div>

    {{-- Grid de 2 columnas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        {{-- Tarjeta de Empleado --}}
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="bg-blue-600 px-4 py-2">
                <h4 class="text-base font-bold text-white">👤 Información del Empleado</h4>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Nombre Completo</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $empleado->nombre_completo }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Cédula</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $empleado->cedula }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Sucursal</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $empleado->sucursal->nombre ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Estado</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $empleado->estado }}</p>
                </div>
            </div>
        </div>

        {{-- Tarjeta de Horario --}}
        @if($horario)
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="bg-purple-600 px-4 py-2">
                <h4 class="text-base font-bold text-white">🕒 Horario Asignado</h4>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Nombre del Horario</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $horario->nombre ?? 'N/A' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Entrada</p>
                        <p class="text-base font-bold text-green-700">
                            {{ \Carbon\Carbon::parse($horario->hora_entrada)->format('h:i A') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase">Salida</p>
                        <p class="text-base font-bold text-red-700">
                            {{ \Carbon\Carbon::parse($horario->hora_salida)->format('h:i A') }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Tolerancia</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $horario->tolerancia_entrada ?? $horario->minutos_tolerancia ?? 0 }} minutos</p>
                </div>

                @if($horario->descripcion)
                <div>
                    <p class="text-xs text-gray-500 uppercase">Descripción</p>
                    <p class="text-sm text-gray-700">{{ $horario->descripcion }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Tarjeta de Detalles de Registro --}}
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="bg-slate-700 px-4 py-2">
            <h4 class="text-base font-bold text-white">📋 Detalles del Registro</h4>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Hora de Registro</p>
                    <p class="text-base font-bold text-gray-800">
                        {{ $asistencia->hora_entrada ?? \Carbon\Carbon::parse($asistencia->hora_registro)->format('h:i:s A') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">
                        @if($asistencia->estado === 'Tarde')
                            Minutos de Retraso
                        @else
                            Diferencia
                        @endif
                    </p>
                    <p class="text-base font-bold {{ 
                        $asistencia->estado === 'Puntual' ? 'text-green-700' : 
                        ($asistencia->estado === 'Tarde' ? 'text-yellow-700' : 'text-red-700') 
                    }}">
                        {{ $asistencia->minutos_retraso ?? abs($asistencia->minutos_diferencia ?? 0) }} minutos
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Tipo de Registro</p>
                    <p class="text-base font-semibold text-gray-800">{{ $asistencia->tipo }}</p>
                </div>
            </div>

            @if($asistencia->justificado || $asistencia->justificada)
            <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-500 rounded">
                <h5 class="font-bold text-amber-800 text-sm mb-1">⚠️ Asistencia Justificada</h5>
                @if($asistencia->justificacion || $asistencia->motivo_justificacion)
                    <p class="text-sm text-amber-700">
                        <span class="font-semibold">Motivo:</span> {{ $asistencia->justificacion ?? $asistencia->motivo_justificacion }}
                    </p>
                @endif
                @if($asistencia->justificadoPor)
                    <p class="text-xs text-amber-600 mt-1">
                        Justificado por: {{ $asistencia->justificadoPor->getFilamentName() }}
                    </p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
        
