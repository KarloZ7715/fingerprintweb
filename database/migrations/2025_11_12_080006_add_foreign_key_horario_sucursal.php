<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            // Primero, cambiar el tipo de sucursal_id para que coincida
            $table->integer('sucursal_id')->nullable()->change();
            
            // Agregar foreign key a sucursal
            $table->foreign('sucursal_id', 'fk_horario_sucursal')
                ->references('id')
                ->on('sucursal')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->dropForeign('fk_horario_sucursal');
        });
    }
};
