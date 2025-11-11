<div x-data="{ 
        state: @entangle('enrollmentState'),
        progress: @entangle('enrollmentProgress'),
        message: @entangle('enrollmentMessage'),
        attempt: @entangle('currentAttempt'),
        maxAttempts: @entangle('maxAttempts'),
        assignedSlot: @entangle('assignedSlotId'),
        isPolling: @entangle('isPolling'),
        pollingInterval: null
    }" x-init="
        // Iniciar polling solo cuando isPolling sea true
        $watch('isPolling', value => {
            if (value && !pollingInterval) {
                pollingInterval = setInterval(() => {
                    $wire.pollEnrollmentStatus();
                }, 2000);
            } else if (!value && pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        });
    " class="w-full relative pointer-events-auto">
    {{-- Contenedor principal --}}
    <div
        class="bg-white dark:bg-slate-900 rounded-xl p-8 border border-slate-200 dark:border-slate-700 shadow-lg max-w-7xl mx-auto">

        {{-- Layout principal: 2 columnas FORZADAS (sin breakpoint) --}}
        <div style="display: flex; gap: 3rem; align-items: flex-start;">

            {{-- COLUMNA IZQUIERDA: SVG de huella --}}
            <div style="width: 320px; flex-shrink: 0;" class="flex flex-col items-center gap-6 pointer-events-none">
                <div class="relative" style="width: 240px; height: 240px; max-width: 240px; max-height: 240px;">
                    {{-- Círculo de fondo con pulso --}}
                    <div class="absolute inset-0 rounded-full transition-all duration-700 pointer-events-none" :class="{
                            'bg-blue-100 dark:bg-blue-900/30 animate-pulse': state === 'enrolling',
                            'bg-green-100 dark:bg-green-900/30': state === 'success',
                            'bg-red-100 dark:bg-red-900/30': state === 'error',
                            'bg-slate-100 dark:bg-slate-800': state === 'idle'
                        }"></div>

                    {{-- SVG de huella que se llena progresivamente --}}
                    <svg viewBox="0 0 240 240" class="relative z-10 pointer-events-none"
                        style="width: 100%; height: 100%; max-width: 240px; max-height: 240px;">
                        {{-- Círculo de progreso exterior --}}
                        <circle cx="120" cy="120" r="110" fill="none" stroke="currentColor" :class="{
                            'text-blue-200 dark:text-blue-800': state === 'enrolling',
                            'text-green-200 dark:text-green-800': state === 'success',
                            'text-red-200 dark:text-red-800': state === 'error',
                            'text-slate-200 dark:text-slate-700': state === 'idle'
                        }" stroke-width="4" class="transition-colors duration-500" />

                        {{-- Círculo de progreso activo --}}
                        <circle cx="120" cy="120" r="110" fill="none" stroke="currentColor" :class="{
                            'text-blue-500 dark:text-blue-400': state === 'enrolling',
                            'text-green-500 dark:text-green-400': state === 'success',
                            'text-red-500 dark:text-red-400': state === 'error',
                            'text-slate-400 dark:text-slate-600': state === 'idle'
                        }" stroke-width="6" stroke-linecap="round" :stroke-dasharray="`${691 * (progress / 100)} 691`"
                            transform="rotate(-90 120 120)" class="transition-all duration-700 ease-out" />

                        {{-- Icono de huella dactilar --}}
                        <g transform="translate(120, 120)">
                            {{-- Líneas de huella (8 curvas) --}}
                            <g :class="{
                                'text-blue-600 dark:text-blue-400': state === 'enrolling',
                                'text-green-600 dark:text-green-400': state === 'success',
                                'text-red-600 dark:text-red-400': state === 'error',
                                'text-slate-400 dark:text-slate-500': state === 'idle'
                            }" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                class="transition-colors duration-500">
                                {{-- Centro --}}
                                <ellipse cx="0" cy="0" rx="12" ry="18" opacity="0.9" />

                                {{-- Capa 1 --}}
                                <path d="M -20,-25 Q -20,-10 -20,10 T -20,30" opacity="0.85"
                                    :opacity="progress >= 15 ? 0.85 : 0.2" />
                                <path d="M 20,-25 Q 20,-10 20,10 T 20,30" opacity="0.85"
                                    :opacity="progress >= 15 ? 0.85 : 0.2" />

                                {{-- Capa 2 --}}
                                <path d="M -35,-30 Q -35,-5 -35,20 T -35,40" opacity="0.75"
                                    :opacity="progress >= 35 ? 0.75 : 0.2" />
                                <path d="M 35,-30 Q 35,-5 35,20 T 35,40" opacity="0.75"
                                    :opacity="progress >= 35 ? 0.75 : 0.2" />

                                {{-- Capa 3 --}}
                                <path d="M -50,-35 Q -50,0 -50,30 T -50,50" opacity="0.65"
                                    :opacity="progress >= 55 ? 0.65 : 0.2" />
                                <path d="M 50,-35 Q 50,0 50,30 T 50,50" opacity="0.65"
                                    :opacity="progress >= 55 ? 0.65 : 0.2" />

                                {{-- Capa 4 (exterior) --}}
                                <path d="M -65,-40 Q -65,5 -65,40 T -65,60" opacity="0.5"
                                    :opacity="progress >= 75 ? 0.5 : 0.2" />
                                <path d="M 65,-40 Q 65,5 65,40 T 65,60" opacity="0.5"
                                    :opacity="progress >= 75 ? 0.5 : 0.2" />
                            </g>

                            {{-- Checkmark cuando success --}}
                            <g x-show="state === 'success'" x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 scale-0"
                                x-transition:enter-end="opacity-100 scale-100">
                                <circle cx="0" cy="0" r="35" fill="white" opacity="0.9" />
                                <path d="M -15,0 L -5,15 L 20,-15" fill="none" stroke="currentColor"
                                    class="text-green-600 dark:text-green-400" stroke-width="5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </g>

                            {{-- X cuando error --}}
                            <g x-show="state === 'error'" x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 scale-0"
                                x-transition:enter-end="opacity-100 scale-100">
                                <circle cx="0" cy="0" r="35" fill="white" opacity="0.9" />
                                <path d="M -15,-15 L 15,15 M 15,-15 L -15,15" fill="none" stroke="currentColor"
                                    class="text-red-600 dark:text-red-400" stroke-width="5" stroke-linecap="round" />
                            </g>
                        </g>
                    </svg>

                    {{-- Porcentaje en el centro del círculo --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none"
                        x-show="state === 'enrolling' && progress < 100" x-transition>
                        <div class="text-3xl font-bold transition-colors duration-500 text-blue-700 dark:text-blue-300"
                            x-text="progress + '%'"></div>
                    </div>
                </div>

                {{-- Indicador de progreso textual debajo del SVG con más separación --}}
                <div class="text-center pointer-events-none" x-show="state === 'enrolling'" x-transition>
                    <div style="font-size: 1rem !important; margin-top: 1.5rem !important;"
                        class="font-medium text-slate-600 dark:text-slate-400">
                        Intento <span x-text="attempt"></span> de <span x-text="maxAttempts"></span>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Contenido completo --}}
            <div style="flex: 1; min-width: 0;">

                {{-- Botones de acción --}}
                <div class="mb-8 space-y-4">
                    {{-- Botón Iniciar/Reintentar --}}
                    <button type="button" wire:click="startEnrollment" x-show="state === 'idle' || state === 'error'"
                        :disabled="state === 'enrolling'" x-transition
                        style="padding: 1.5rem 2rem !important; gap: 1rem !important;"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white text-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center pointer-events-auto cursor-pointer">
                        <svg style="width: 32px !important; height: 32px !important; min-width: 32px !important; min-height: 32px !important; max-width: 32px !important; max-height: 32px !important;"
                            class="flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="state === 'error' ? 'Reintentar Registro' : 'Iniciar Registro'"></span>
                    </button>

                    {{-- Botón Cancelar --}}
                    <button type="button" wire:click="cancelEnrollment" x-show="state === 'enrolling'" x-transition
                        class="w-full px-8 py-4 bg-red-600 hover:bg-red-700 text-white text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-4 pointer-events-auto cursor-pointer">
                        <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; max-width: 24px; max-height: 24px;"
                            class="flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Cancelar Registro</span>
                    </button>

                    {{-- Indicador de éxito --}}
                    <div x-show="state === 'success'" x-transition
                        class="w-full px-8 py-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 text-green-700 dark:text-green-300 text-lg font-semibold rounded-xl flex items-center justify-center gap-4 pointer-events-none">
                        <svg style="width: 26px; height: 26px; min-width: 26px; min-height: 26px; max-width: 26px; max-height: 26px;"
                            class="flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>¡Huella Registrada Correctamente!</span>
                    </div>
                </div>

                {{-- Sección de estado y recomendaciones --}}
                <div class="space-y-8">

                    {{-- Estado actual con tarjeta --}}
                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-7 border border-slate-200 dark:border-slate-700 pointer-events-none">
                        {{-- Título de sección --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1.5 h-6 rounded-full" :class="{
                                'bg-blue-500': state === 'enrolling',
                                'bg-green-500': state === 'success',
                                'bg-red-500': state === 'error',
                                'bg-slate-400': state === 'idle'
                            }"></div>
                            <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-300">Estado del Registro
                            </h4>
                        </div>

                        {{-- Mensaje de estado --}}
                        <div style="margin-bottom: 1.5rem !important;">
                            <p style="font-size: 1.125rem !important; line-height: 1.8 !important; margin: 0 !important;"
                                class="transition-colors duration-500" :class="{
                                'text-blue-700 dark:text-blue-300': state === 'enrolling',
                                'text-green-700 dark:text-green-300': state === 'success',
                                'text-red-700 dark:text-red-300': state === 'error',
                                'text-slate-600 dark:text-slate-400': state === 'idle'
                            }" x-text="message"></p>
                        </div>

                        {{-- Indicador de sincronización --}}
                        <div class="flex items-center gap-3 text-base text-blue-600 dark:text-blue-400 pt-6 mt-6 border-t border-slate-200 dark:border-slate-700"
                            x-show="isPolling" x-transition>
                            <div class="relative w-5 h-5 flex-shrink-0">
                                <div
                                    class="absolute inset-0 rounded-full border-2 border-blue-200 dark:border-blue-800">
                                </div>
                                <div
                                    class="absolute inset-0 rounded-full border-2 border-t-blue-600 dark:border-t-blue-400 animate-spin">
                                </div>
                            </div>
                            <span>Sincronizando con sensor...</span>
                        </div>
                    </div>

                    {{-- Tips --}}
                    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl p-7 pointer-events-none"
                        x-show="state === 'idle' || state === 'enrolling'" x-transition>
                        <div class="flex gap-4">
                            <svg style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px;"
                                class="text-amber-600 dark:text-amber-400 flex-shrink-0 mt-1" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-amber-900 dark:text-amber-200 flex-1">
                                <p style="margin-bottom: 1.25rem !important;" class="font-semibold text-lg">
                                    Recomendaciones:</p>
                                <ul style="display: flex; flex-direction: column; gap: 0.875rem;">
                                    <li style="line-height: 1.7; font-size: 1rem;">• Asegúrese de que el dedo esté
                                        limpio y seco</li>
                                    <li style="line-height: 1.7; font-size: 1rem;">• Presione firmemente sobre el sensor
                                    </li>
                                    <li style="line-height: 1.7; font-size: 1rem;">• Mantenga el dedo completamente
                                        inmóvil</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>