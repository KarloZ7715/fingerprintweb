<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('huella', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_huella')->unique();
            $table->foreignId('empleado_id')->constrained('empleado')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('huella');
    }
};
