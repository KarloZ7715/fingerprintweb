<?php

namespace App\Filament\Resources\AsistenciaResource\Pages;

use App\Filament\Resources\AsistenciaResource;
use App\Filament\Widgets\AsistenciasStatsWidget;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;
    /**
     * Acciones en el encabezado de la página
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('detectar_ausencias')
                ->label('Detectar Ausencias')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Detectar Ausencias del Día')
                ->modalDescription('Se detectarán automáticamente los empleados que no han marcado entrada el día de hoy')
                ->action(function () {
                    $service = app(AsistenciaService::class);
                    $ausencias = $service->detectarAusencias();
                    
                    Notification::make()
                        ->success()
                        ->title('Detección completada')
                        ->body('Se detectaron ' . count($ausencias) . ' ausencias')
                        ->send();
                }),

            Actions\Action::make('estadisticas')
                ->label('Estadísticas del Día')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Estadísticas de Asistencias - ' . Carbon::now('America/Bogota')->format('d/m/Y'))
                ->modalContent(function () {
                    $service = app(AsistenciaService::class);
                    $stats = $service->obtenerEstadisticas();
                    
                    return view('filament.pages.estadisticas-asistencias', [
                        'stats' => $stats,
                        'fecha' => Carbon::now('America/Bogota')->format('d/m/Y'),
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalWidth('3xl'),
        ];
    }

    /**
     * Widgets en el encabezado de la página
     */
    protected function getHeaderWidgets(): array
    {
        return [
            AsistenciasStatsWidget::class,
        ];
    }
}
