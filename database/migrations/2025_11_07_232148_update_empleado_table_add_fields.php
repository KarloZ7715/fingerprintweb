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
        Schema::table('empleado', function (Blueprint $table) {
            // Agregar nombres completos
            $table->string('segundo_nombre', 50)->nullable()->after('primer_nombre');
            $table->string('segundo_apellido', 50)->nullable()->after('primer_apellido');

            // Agregar contacto
            $table->string('email', 100)->nullable()->after('telefono');

            // Agregar relación con horario
            $table->unsignedInteger('horario_id')->nullable()->after('sucursal_id');

            // Agregar foto
            $table->string('foto_url', 255)->nullable()->after('horario_id');

            // Modificar estado a ENUM con más opciones
            $table->enum('estado', ['Activo', 'Inactivo', 'Suspendido', 'Vacaciones'])
                ->default('Activo')
                ->change();

            // Agregar updated_at
            $table->timestamp('updated_at')->nullable()->after('created_at');

            // Agregar índices y foreign keys
            $table->unique('cedula', 'unique_empleado_cedula');
            $table->index('horario_id', 'idx_empleado_horario');

            // Foreign key a horario (se agregará después de crear tabla horario)
            // $table->foreign('horario_id', 'fk_empleado_horario')
            //     ->references('id')->on('horario')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            // Eliminar foreign key
            // $table->dropForeign('fk_empleado_horario');

            // Eliminar índices
            $table->dropUnique('unique_empleado_cedula');
            $table->dropIndex('idx_empleado_horario');

            // Eliminar columnas
            $table->dropColumn([
                'segundo_nombre',
                'segundo_apellido',
                'email',
                'horario_id',
                'foto_url',
                'updated_at'
            ]);

            // Revertir estado a VARCHAR
            $table->string('estado', 20)->nullable()->change();
        });
    }
};
