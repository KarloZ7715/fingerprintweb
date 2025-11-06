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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // Basado en RF2 (Registrar Empleado)
            $table->string('name');
            $table->string('document')->unique(); // Documento único
            $table->string('email')->unique()->nullable(); // Email único, puede ser nulo
            // Puedes añadir más campos aquí (cargo, etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};