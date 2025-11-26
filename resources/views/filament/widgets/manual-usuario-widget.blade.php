<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Contenedor Flex Row forzado con estilos inline para asegurar alineación horizontal --}}
        <div style="display: flex; flex-direction: row; align-items: center; gap: 1rem;">
            {{-- Ícono a la izquierda --}}
            <div style="flex-shrink: 0;">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-600 text-white shadow-md">
                    <svg style="width: 1.5rem; height: 1.5rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
            </div>

            {{-- Textos a la derecha --}}
            <div style="flex: 1; min-width: 0;">
                <h2 class="text-lg font-bold text-gray-950 dark:text-white leading-tight">
                    Manual de Usuario
                </h2>
            </div>
        </div>

        {{-- Botón debajo, separado --}}
        <div style="margin-top: 1.25rem;">
            <a 
                href="{{ $this->getManualUrl() }}" 
                target="_blank"
                class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                style="display: flex; flex-direction: row; align-items: center; justify-content: flex-start; gap: 0.5rem;"
            >
                <svg style="width: 1rem; height: 1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
                <span>Abrir manual</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
