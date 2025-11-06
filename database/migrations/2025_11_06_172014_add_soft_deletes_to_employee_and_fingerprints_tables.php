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
        // 1. Agregar 'deleted_at' a la tabla de empleados
        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // 2. Agregar 'deleted_at' a la tabla 'huella' (o 'fingerprints')
        // !! CAMBIA 'huella' si tu tabla se llama 'fingerprints' !!
        Schema::table('huella', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('huella', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
