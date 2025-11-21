<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- HEADER PROFESIONAL -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6 border-l-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        📊 Reporte de Asistencias, Faltas y Retrasos
                    </h1>
                    <p class="text-gray-600 text-sm max-w-3xl">
                        Análisis detallado del desempeño laboral del personal. Este informe proporciona una visión 
                        clara y accionable del comportamiento de asistencia, permitiendo identificar empleados 
                        destacados y áreas de mejora.
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Generado el</p>
                    <p class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- FILTROS PROFESIONALES -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Mes</label>
                    <select wire:model="mes" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ \Carbon\Carbon::createFromFormat('m', $i)->monthName }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📆 Año</label>
                    <select wire:model="anio" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        @for($i = 2024; $i <= 2030; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1 pt-7">
                    <button wire:click="generarReportes" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition transform hover:scale-105">
                        🔄 Generar Reporte
                    </button>
                </div>
                <div class="flex-1 pt-7">
                    <button wire:click="descargarPDF" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition transform hover:scale-105">
                        📄 Descargar PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- TABLA ASISTENCIAS -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    Tabla de Asistencias por Empleado
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Cédula</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Empleado</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Asistencias</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Ausencias</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Tasa (%)</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($reporteAsistencias as $fila)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $fila->cedula }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $fila->empleado }}</td>
                                <td class="px-6 py-4 text-sm text-center text-gray-700 font-bold">{{ $fila->asistencias ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-center text-gray-700 font-bold">{{ $fila->ausencias ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="font-bold text-lg {{ ($fila->tasa ?? 0) >= 95 ? 'text-green-600' : (($fila->tasa ?? 0) >= 90 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($fila->tasa ?? 0, 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="px-4 py-2 rounded-full text-xs font-bold text-white inline-block
                                        {{ $fila->estado === 'EXCELENTE' ? 'bg-green-500' : ($fila->estado === 'ALERTA' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                        {{ $fila->estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        No hay datos disponibles para el período seleccionado
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABLA RETRASOS -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Tabla Detallada de Retrasos
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Cédula</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Empleado</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Promedio (min)</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Máximo (min)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($reporteRetrasos as $fila)
                            <tr class="hover:bg-yellow-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $fila->cedula }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $fila->empleado }}</td>
                                <td class="px-6 py-4 text-sm text-center font-bold text-gray-700">{{ $fila->cantidad }}</td>
                                <td class="px-6 py-4 text-sm text-center font-bold text-orange-600">{{ number_format($fila->promedio, 1) }}</td>
                                <td class="px-6 py-4 text-sm text-center font-bold text-red-600">{{ $fila->maximo }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay retrasos registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico 1: Tasa Asistencia -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Tasa de Asistencia (%)
                </h3>
                <canvas id="chartAsistencias" class="w-full" style="max-height: 300px;"></canvas>
            </div>

            <!-- Gráfico 2: Ausencias -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    Total de Ausencias
                </h3>
                <canvas id="chartAusencias" class="w-full" style="max-height: 300px;"></canvas>
            </div>

            <!-- Gráfico 3: Retrasos -->
            <div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Promedio de Retrasos (minutos)
                </h3>
                <canvas id="chartRetrasos" class="w-full" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- FOOTER INFO -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-bold text-blue-900 mb-1">Información Importante</h4>
                    <p class="text-xs text-blue-800 leading-relaxed">
                        Los datos permiten identificar empleados con desempeño destacado y aquellos que requieren 
                        seguimiento por ausencias, retrasos o bajo cumplimiento. Este reporte facilita la toma de 
                        decisiones y mejora del control laboral. La información es confidencial y debe ser utilizada 
                        únicamente con fines administrativos.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- CHART.JS SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartAsistencias = null;
        let chartAusencias = null;
        let chartRetrasos = null;

        // Función para crear/actualizar gráficos
        function actualizarGraficos(datos) {
            // Destruir gráficos existentes
            if (chartAsistencias) chartAsistencias.destroy();
            if (chartAusencias) chartAusencias.destroy();
            if (chartRetrasos) chartRetrasos.destroy();

            // Gráfico 1: Asistencias
            if (datos.asistencias && datos.asistencias.labels.length > 0) {
                const ctxAsistencias = document.getElementById('chartAsistencias');
                if (ctxAsistencias) {
                    chartAsistencias = new Chart(ctxAsistencias, {
                        type: 'bar',
                        data: {
                            labels: datos.asistencias.labels,
                            datasets: [{
                                label: 'Tasa de Asistencia (%)',
                                data: datos.asistencias.data,
                                backgroundColor: datos.asistencias.backgroundColor,
                                borderColor: datos.asistencias.backgroundColor.map(c => c.replace('0.7', '1')),
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `${context.parsed.x}%`
                                    }
                                }
                            },
                            scales: {
                                x: { 
                                    max: 100,
                                    ticks: { callback: (val) => val + '%' }
                                }
                            }
                        }
                    });
                }
            }

            // Gráfico 2: Ausencias
            if (datos.ausencias && datos.ausencias.labels.length > 0) {
                const ctxAusencias = document.getElementById('chartAusencias');
                if (ctxAusencias) {
                    chartAusencias = new Chart(ctxAusencias, {
                        type: 'pie',
                        data: {
                            labels: datos.ausencias.labels,
                            datasets: [{
                                data: datos.ausencias.data,
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.7)',
                                    'rgba(251, 146, 60, 0.7)',
                                    'rgba(234, 179, 8, 0.7)',
                                    'rgba(34, 197, 94, 0.7)',
                                    'rgba(59, 130, 246, 0.7)',
                                    'rgba(168, 85, 247, 0.7)',
                                ],
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
            }

            // Gráfico 3: Retrasos
            if (datos.retrasos && datos.retrasos.labels.length > 0) {
                const ctxRetrasos = document.getElementById('chartRetrasos');
                if (ctxRetrasos) {
                    chartRetrasos = new Chart(ctxRetrasos, {
                        type: 'bar',
                        data: {
                            labels: datos.retrasos.labels,
                            datasets: [{
                                label: 'Promedio Retrasos (min)',
                                data: datos.retrasos.data,
                                backgroundColor: 'rgba(251, 146, 60, 0.7)',
                                borderColor: 'rgba(251, 146, 60, 1)',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                }
            }
        }

        // Inicializar gráficos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const datosIniciales = {
                asistencias: @json($this->prepararDatosAsistencias()),
                ausencias: @json($this->prepararDatosAusencias()),
                retrasos: @json($this->prepararDatosRetrasos())
            };
            actualizarGraficos(datosIniciales); 
        });

        // Escuchar evento de Livewire para actualizar gráficos
        document.addEventListener('livewire:init', () => {
            Livewire.on('actualizarGraficos', (event) => {
                actualizarGraficos(event[0]);
            });
        });
    </script>
</div>