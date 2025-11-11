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
            // Agregar campo codigo_pais antes de telefono
            $table->string('codigo_pais', 4)
                ->default('57')
                ->after('segundo_apellido')
                ->comment('Código de país para teléfono (ej: 57 para Colombia)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->dropColumn('codigo_pais');
        });
    }
};
