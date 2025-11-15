<div class="space-y-4">
    {{-- Tarjetas de estadísticas principales --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
            <div class="text-2xl font-bold text-green-700 mb-1">{{ $stats['puntuales'] }}</div>
            <div class="text-xs text-green-600 uppercase">✅ Puntuales</div>
        </div>

        <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
            <div class="text-2xl font-bold text-yellow-700 mb-1">{{ $stats['tarde'] }}</div>
            <div class="text-xs text-yellow-600 uppercase">⏰ Tardanzas</div>
        </div>

        <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-500">
            <div class="text-2xl font-bold text-red-700 mb-1">{{ $stats['ausentes'] }}</div>
            <div class="text-xs text-red-600 uppercase">❌ Ausentes</div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
            <div class="text-2xl font-bold text-blue-700 mb-1">{{ $stats['total'] }}</div>
            <div class="text-xs text-blue-600 uppercase">📊 Total</div>
        </div>
    </div>

    {{-- Información detallada --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <h3 class="font-bold text-gray-800 mb-3">📅 {{ $fecha }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span class="text-gray-600">Total registros:</span>
                <span class="font-bold text-gray-800">{{ $stats['total'] }}</span>
            </div>
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span class="text-gray-600">Justificados:</span>
                <span class="font-bold text-gray-800">{{ $stats['justificados'] }}</span>
            </div>
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span class="text-gray-600">Sin justificar:</span>
                <span class="font-bold text-gray-800">{{ $stats['tarde'] + $stats['ausentes'] - $stats['justificados'] }}</span>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if($stats['ausentes'] > 0)
        <div class="bg-red-50 border-l-4 border-red-500 rounded p-3">
            <p class="text-red-800 font-semibold text-sm">
                ⚠️ Se detectaron <span class="text-base">{{ $stats['ausentes'] }}</span> ausencias
            </p>
        </div>
    @endif

    @if($stats['tarde'] > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded p-3">
            <p class="text-yellow-800 font-semibold text-sm">
                ⏱️ <span class="text-base">{{ $stats['tarde'] }}</span> empleados llegaron tarde
            </p>
        </div>
    @endif

    @if($stats['puntuales'] == $stats['total'] && $stats['total'] > 0)
        <div class="bg-green-50 border-l-4 border-green-500 rounded p-3">
            <p class="text-green-800 font-semibold text-sm">
                🎉 ¡Excelente! Todos los empleados fueron puntuales
            </p>
        </div>
    @endif
</div>
