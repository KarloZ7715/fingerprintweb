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
            // Eliminar columna template_huella (almacenamiento inseguro)
            // Los templates quedan almacenados únicamente en el sensor AS608
            $table->dropColumn('template_huella');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('huella', function (Blueprint $table) {
            // Restaurar columna en caso de rollback
            $table->text('template_huella')->nullable()->after('mano');
        });
    }
};
