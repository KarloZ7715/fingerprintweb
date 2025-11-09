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

        {{-- TODO: Componente LiveWire para estado del enrollment --}}
        <x-filament::section>
            <x-slot name="heading">
                Estado del Registro
            </x-slot>

            <div class="text-center py-8">
                <div
                    class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                    <x-heroicon-o-finger-print class="w-12 h-12 text-gray-400" />
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Listo para iniciar el registro de huella dactilar
                </p>

                <x-filament::button wire:click="startEnrollment" color="primary" size="lg">
                    <x-heroicon-o-play class="w-5 h-5 mr-2" />
                    Iniciar Registro
                </x-filament::button>
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