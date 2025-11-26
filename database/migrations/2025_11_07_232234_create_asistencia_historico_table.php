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
        Schema::create('asistencia_historico', function (Blueprint $table) {
            $table->id();

            // Relación con asistencia - usar integer para coincidir con asistencia.id
            $table->integer('asistencia_id')
                ->comment('ID del registro de asistencia modificado');
            $table->integer('empleado_id')
                ->comment('ID del empleado (redundante pero útil)');

            // Campos modificados
            $table->string('campo_modificado', 50)
                ->comment('Nombre del campo que cambió');
            $table->text('valor_anterior')->nullable()
                ->comment('Valor antes del cambio');
            $table->text('valor_nuevo')->nullable()
                ->comment('Valor después del cambio');

            // Auditoría
            $table->integer('modificado_por')->nullable()
                ->comment('ID del admin que hizo el cambio');
            $table->timestamp('fecha_modificacion')->useCurrent()
                ->comment('Cuándo se hizo el cambio');

            // Índices
            $table->index('asistencia_id', 'idx_historico_asistencia');
            $table->index('empleado_id', 'idx_historico_empleado');
            $table->index('modificado_por', 'idx_historico_admin');
            $table->index('fecha_modificacion', 'idx_historico_fecha');

            // Foreign keys
            $table->foreign('asistencia_id', 'fk_historico_asistencia')
                ->references('id')->on('asistencia')
                ->onDelete('cascade');
            $table->foreign('empleado_id', 'fk_historico_empleado')
                ->references('id')->on('empleado')
                ->onDelete('cascade');
            $table->foreign('modificado_por', 'fk_historico_admin')
                ->references('id')->on('administrador')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_historico');
    }
};
