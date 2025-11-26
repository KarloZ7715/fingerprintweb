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
        // Verificar si las columnas ya existen para hacer la migración idempotente
        $hasEmployeeId = Schema::hasColumn('asistencia', 'empleado_id');

        if (!$hasEmployeeId) {
            // Primera ejecución: agregar todas las columnas e índices
            // Primero eliminar la foreign key existente de huella_id si existe
            try {
                Schema::table('asistencia', function (Blueprint $table) {
                    $table->dropForeign('asistencia_ibfk_1');
                });
            } catch (\Exception $e) {
                // La foreign key no existe, continuar
            }

            Schema::table('asistencia', function (Blueprint $table) {
                // Agregar relación directa con empleado
                $table->integer('empleado_id')->after('id')
                    ->comment('Relación directa con empleado');

                // Agregar relación con horario
                $table->unsignedBigInteger('horario_id')->after('empleado_id')
                    ->comment('Horario contra el que se valida');

                // Modificar huella_id para permitir NULL (marcación manual) - mantener como int signed
                $table->integer('huella_id')->nullable()->change();

                // Agregar fecha y hora separados
                $table->date('fecha')->after('horario_id')
                    ->comment('Fecha del registro');
                $table->time('hora_registro')->after('fecha')
                    ->comment('Hora exacta del registro');

                // Modificar estado a ENUM con más opciones
                $table->enum('estado', ['Puntual', 'Tarde', 'Ausente', 'Justificado'])
                    ->default('Puntual')
                    ->change();

                // Modificar tipo a ENUM
                $table->enum('tipo', ['Entrada', 'Salida'])
                    ->change();

                // Agregar minutos de diferencia
                $table->integer('minutos_diferencia')->default(0)->after('tipo')
                    ->comment('Minutos de diferencia con horario esperado');

                // Sistema de justificación
                $table->boolean('justificada')->default(false)->after('observaciones');
                $table->text('motivo_justificacion')->nullable()->after('justificada');
                $table->integer('justificado_por')->nullable()->after('motivo_justificacion')
                    ->comment('Admin que justificó');
                $table->timestamp('fecha_justificacion')->nullable()->after('justificado_por');

                // Metadata
                $table->enum('metodo_registro', ['Huella', 'Manual', 'Emergencia'])
                    ->default('Huella')->after('fecha_justificacion');

                // Timestamps
                $table->timestamp('created_at')->nullable()->after('metodo_registro');
                $table->timestamp('updated_at')->nullable()->after('created_at');

                // Índices
                $table->index('empleado_id', 'idx_asistencia_empleado');
                $table->index('horario_id', 'idx_asistencia_horario');
                $table->index('fecha', 'idx_asistencia_fecha');
                $table->index(['empleado_id', 'fecha'], 'idx_asistencia_empleado_fecha');
                $table->index('estado', 'idx_asistencia_estado');
            });
        }

        // Agregar foreign keys (puede ejecutarse tanto en primera como en segunda ejecución)
        $existingFKs = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'asistencia' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $fkNames = array_column($existingFKs, 'CONSTRAINT_NAME');

        Schema::table('asistencia', function (Blueprint $table) use ($fkNames) {
            // Foreign keys - solo crear si no existen
            if (!in_array('fk_asistencia_empleado', $fkNames)) {
                $table->foreign('empleado_id', 'fk_asistencia_empleado')
                    ->references('id')->on('empleado')
                    ->onDelete('cascade');
            }

            if (!in_array('fk_asistencia_horario', $fkNames)) {
                $table->foreign('horario_id', 'fk_asistencia_horario')
                    ->references('id')->on('horario')
                    ->onDelete('restrict');
            }

            if (!in_array('fk_asistencia_huella', $fkNames)) {
                $table->foreign('huella_id', 'fk_asistencia_huella')
                    ->references('id')->on('huella')
                    ->onDelete('set null');
            }

            if (!in_array('fk_asistencia_admin', $fkNames)) {
                $table->foreign('justificado_por', 'fk_asistencia_admin')
                    ->references('id')->on('administrador')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign('fk_asistencia_empleado');
            $table->dropForeign('fk_asistencia_horario');
            $table->dropForeign('fk_asistencia_huella');
            $table->dropForeign('fk_asistencia_admin');

            // Eliminar índices
            $table->dropIndex('idx_asistencia_empleado');
            $table->dropIndex('idx_asistencia_horario');
            $table->dropIndex('idx_asistencia_fecha');
            $table->dropIndex('idx_asistencia_empleado_fecha');
            $table->dropIndex('idx_asistencia_estado');

            // Eliminar columnas
            $table->dropColumn([
                'empleado_id',
                'horario_id',
                'fecha',
                'hora_registro',
                'minutos_diferencia',
                'justificada',
                'motivo_justificacion',
                'justificado_por',
                'fecha_justificacion',
                'metodo_registro',
                'created_at',
                'updated_at'
            ]);

            // Revertir tipos a VARCHAR
            $table->string('estado', 20)->nullable()->change();
            $table->string('tipo', 10)->nullable()->change();
            $table->integer('huella_id')->change();
        });

        // Recrear la foreign key original
        Schema::table('asistencia', function (Blueprint $table) {
            $table->foreign('huella_id', 'asistencia_ibfk_1')
                ->references('id')->on('huella');
        });
    }
};
