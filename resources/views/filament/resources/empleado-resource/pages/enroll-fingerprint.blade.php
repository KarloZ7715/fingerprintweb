<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Información del Empleado --}}
        <x-filament::section>
            <x-slot name="heading">
                Información del Empleado
            </x-slot>

            <x-slot name="description">
                Datos básicos del empleado que registrará su huella
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->nombre_completo }}</p>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cédula:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->cedula }}</p>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado:</span>
                    <x-filament::badge color="warning">
                        {{ $record->estado }}
                    </x-filament::badge>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->sucursal->nombre }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Instrucciones --}}
        <x-filament::section>
            <x-slot name="heading">
                Instrucciones para Registro de Huella
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <ol class="space-y-2">
                    <li>Asegúrese de que el sensor AS608 esté conectado y encendido</li>
                    <li>Limpie el dedo que desea registrar (preferiblemente dedo índice)</li>
                    <li>Haga clic en el botón "Iniciar Registro"</li>
                    <li>Coloque el dedo en el sensor cuando se le indique</li>
                    <li>Retire el dedo cuando se le solicite</li>
                    <li>Repita el proceso 2 veces más para confirmar</li>
                    <li>Espere la confirmación de registro exitoso</li>
                </ol>

                <div
                    class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Nota:</strong> El proceso completo toma aproximadamente 30-60 segundos.
                        Mantenga el dedo limpio y seco para mejores resultados.
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Componente LiveWire para estado del enrollment --}}
        <x-filament::section wire:poll.2s="pollEnrollmentStatus">
            <x-slot name="heading">
                Estado del Registro
            </x-slot>

            <div class="text-center py-8">
                {{-- Icono animado según estado --}}
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4
                    @if($enrollmentState === 'enrolling') bg-blue-100 dark:bg-blue-900/20 animate-pulse
                    @elseif($enrollmentState === 'success') bg-green-100 dark:bg-green-900/20
                    @elseif($enrollmentState === 'error') bg-red-100 dark:bg-red-900/20
                    @else bg-gray-100 dark:bg-gray-800
                    @endif">

                    @if($enrollmentState === 'enrolling')
                        <x-heroicon-o-finger-print class="w-12 h-12 text-blue-500 animate-pulse" />
                    @elseif($enrollmentState === 'success')
                        <x-heroicon-o-check-circle class="w-12 h-12 text-green-500" />
                    @elseif($enrollmentState === 'error')
                        <x-heroicon-o-x-circle class="w-12 h-12 text-red-500" />
                    @else
                        <x-heroicon-o-finger-print class="w-12 h-12 text-gray-400" />
                    @endif
                </div>

                {{-- Barra de progreso --}}
                @if($enrollmentState === 'enrolling' || $enrollmentState === 'success')
                    <div class="w-full max-w-md mx-auto mb-4">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Progreso
                            </span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $enrollmentProgress }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                                style="width: {{ $enrollmentProgress }}%"></div>
                        </div>
                    </div>
                @endif

                {{-- Mensaje de estado --}}
                <p class="text-sm mb-4
                    @if($enrollmentState === 'enrolling') text-blue-600 dark:text-blue-400 font-medium
                    @elseif($enrollmentState === 'success') text-green-600 dark:text-green-400 font-medium
                    @elseif($enrollmentState === 'error') text-red-600 dark:text-red-400 font-medium
                    @else text-gray-600 dark:text-gray-400
                    @endif">
                    {{ $enrollmentMessage }}
                </p>

                {{-- Info del slot asignado --}}
                @if($assignedSlotId !== null)
                    <div class="mb-4">
                        <x-filament::badge color="info">
                            Slot asignado: #{{ $assignedSlotId }}
                        </x-filament::badge>
                    </div>
                @endif

                {{-- Botones según estado --}}
                <div class="flex gap-4 justify-center">
                    @if($enrollmentState === 'idle' || $enrollmentState === 'error')
                        <x-filament::button wire:click="startEnrollment" color="primary" size="lg"
                            :disabled="$enrollmentState === 'enrolling'">
                            <x-heroicon-o-play class="w-5 h-5 mr-2" />
                            {{ $enrollmentState === 'error' ? 'Reintentar Registro' : 'Iniciar Registro' }}
                        </x-filament::button>
                    @endif

                    @if($enrollmentState === 'enrolling')
                        <x-filament::button wire:click="cancelEnrollment" color="danger" outlined size="lg">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                            Cancelar Registro
                        </x-filament::button>
                    @endif

                    @if($enrollmentState === 'success')
                        <x-filament::button color="success" size="lg" tag="a" :href="EmpleadoResource::getUrl('index')">
                            <x-heroicon-o-check class="w-5 h-5 mr-2" />
                            Ir a Lista de Empleados
                        </x-filament::button>
                    @endif
                </div>

                {{-- Indicador visual de polling activo --}}
                @if($isPolling)
                    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-500">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <span>Sincronizando con sensor...</span>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Botones de Acción --}}
        <div class="flex justify-between">
            <x-filament::button color="gray" tag="a" :href="EmpleadoResource::getUrl('index')">
                Volver a la Lista
            </x-filament::button>

            <x-filament::button color="danger" outlined wire:click="skipEnrollment">
                Registrar Después
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>