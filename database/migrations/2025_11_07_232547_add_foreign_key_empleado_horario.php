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
            // Agregar foreign key a horario (ahora que la tabla horario existe)
            $table->foreign('horario_id', 'fk_empleado_horario')
                ->references('id')->on('horario')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            // Eliminar foreign key
            $table->dropForeign('fk_empleado_horario');
        });
    }
};
