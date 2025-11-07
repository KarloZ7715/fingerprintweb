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
        Schema::create('administrador', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); // Podría ser el campo de login principal
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            // La migración de "update" añadirá el email, primer_apellido, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrador');
    }
};