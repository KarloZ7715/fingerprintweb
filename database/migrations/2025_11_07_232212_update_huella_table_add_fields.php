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
        Schema::table('huella', function (Blueprint $table) {
            // Agregar posición en sensor AS608
            $table->integer('numero_slot')->unique()->after('id')
                ->comment('Posición en memoria del sensor (1-127)');

            // Identificación del dedo
            $table->enum('tipo_dedo', ['Pulgar', 'Indice', 'Medio', 'Anular', 'Meñique'])
                ->default('Indice')->after('numero_slot');
            $table->enum('mano', ['Izquierda', 'Derecha'])
                ->default('Derecha')->after('tipo_dedo');

            // Renombrar codigo_huella a template_huella
            $table->renameColumn('codigo_huella', 'template_huella');

            // Metadata del enrolamiento
            $table->integer('enrolado_por')->nullable()->after('fecha_enrolamiento')
                ->comment('ID del admin que enroló');
            $table->integer('calidad')->nullable()->after('enrolado_por')
                ->comment('Calidad de la huella (1-100)');

            // Modificar estado a ENUM
            $table->enum('estado', ['Activa', 'Inactiva', 'Bloqueada'])
                ->default('Activa')
                ->change();

            // Agregar timestamps solo si no existen
            if (!Schema::hasColumn('huella', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('huella', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }

            // Índice para numero_slot (los otros ya existen)
            $table->index('numero_slot', 'idx_huella_slot');

            // Foreign key a administrador
            $table->foreign('enrolado_por', 'fk_huella_admin')
                ->references('id')->on('administrador')
                ->onDelete('set null');
        });

        // Crear índices solo si no existen (algunos ya están en la BD)
        $indexes = collect(\DB::select("SHOW INDEX FROM huella"))->pluck('Key_name')->toArray();

        if (!in_array('idx_huella_empleado', $indexes)) {
            \DB::statement('ALTER TABLE huella ADD INDEX idx_huella_empleado (empleado_id)');
        }

        if (!in_array('idx_huella_estado', $indexes)) {
            \DB::statement('ALTER TABLE huella ADD INDEX idx_huella_estado (estado)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('huella', function (Blueprint $table) {
            // Eliminar foreign key
            $table->dropForeign('fk_huella_admin');

            // Eliminar índices
            $table->dropIndex('idx_huella_empleado');
            $table->dropIndex('idx_huella_slot');
            $table->dropIndex('idx_huella_estado');
            $table->dropUnique(['numero_slot']);

            // Renombrar de vuelta
            $table->renameColumn('template_huella', 'codigo_huella');

            // Eliminar columnas
            $table->dropColumn([
                'numero_slot',
                'tipo_dedo',
                'mano',
                'enrolado_por',
                'calidad',
                'created_at',
                'updated_at'
            ]);

            // Revertir estado a VARCHAR
            $table->string('estado', 20)->nullable()->change();
        });
    }
};
