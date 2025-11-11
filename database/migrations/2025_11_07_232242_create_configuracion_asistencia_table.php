<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_asistencia', function (Blueprint $table) {
            $table->id();

            // Relación con sucursal (opcional, si NULL = global) - usar integer para coincidir con sucursal.id
            $table->integer('sucursal_id')->nullable()
                ->comment('NULL = configuración global');

            // Tolerancias globales (minutos)
            $table->integer('tolerancia_entrada_global')->default(15)
                ->comment('Minutos permitidos de retraso por defecto');
            $table->integer('tolerancia_salida_global')->default(15)
                ->comment('Minutos permitidos de salida temprana por defecto');

            // Configuración de marcaciones
            $table->boolean('requiere_marcacion_salida')->default(true)
                ->comment('Si es obligatorio marcar salida');
            $table->boolean('permite_marcacion_manual')->default(false)
                ->comment('Si admin puede marcar manualmente');
            $table->boolean('requiere_justificacion_ausencia')->default(true)
                ->comment('Si ausencias deben ser justificadas');

            // Notificaciones
            $table->boolean('notificar_tardanzas')->default(true)
                ->comment('Enviar notificaciones por tardanzas');
            $table->boolean('notificar_ausencias')->default(true)
                ->comment('Enviar notificaciones por ausencias');
            $table->string('email_notificaciones', 100)->nullable()
                ->comment('Email para recibir notificaciones');

            // Reportes automáticos
            $table->boolean('generar_reporte_diario')->default(false)
                ->comment('Generar reporte al final del día');
            $table->boolean('generar_reporte_semanal')->default(true)
                ->comment('Generar reporte semanal');

            // Timestamps
            $table->timestamps();

            // Índices
            $table->unique('sucursal_id', 'unique_config_sucursal');

            // Foreign key
            $table->foreign('sucursal_id', 'fk_config_sucursal')
                ->references('id')->on('sucursal')
                ->onDelete('cascade');
        });

        // Insertar configuración global por defecto
        DB::table('configuracion_asistencia')->insert([
            'sucursal_id' => null,
            'tolerancia_entrada_global' => 15,
            'tolerancia_salida_global' => 15,
            'requiere_marcacion_salida' => true,
            'permite_marcacion_manual' => false,
            'requiere_justificacion_ausencia' => true,
            'notificar_tardanzas' => true,
            'notificar_ausencias' => true,
            'email_notificaciones' => null,
            'generar_reporte_diario' => false,
            'generar_reporte_semanal' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_asistencia');
    }
};
