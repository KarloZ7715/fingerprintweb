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
        // Add Spanish-named columns expected by the app if they don't already exist.
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'cedula')) {
                $table->string('cedula')->unique()->nullable()->after('id');
            }

            if (! Schema::hasColumn('employees', 'primer_nombre')) {
                $table->string('primer_nombre')->nullable()->after('cedula');
            }

            if (! Schema::hasColumn('employees', 'primer_apellido')) {
                $table->string('primer_apellido')->nullable()->after('primer_nombre');
            }

            if (! Schema::hasColumn('employees', 'telefono')) {
                $table->string('telefono')->nullable()->after('primer_apellido');
            }

            if (! Schema::hasColumn('employees', 'estado')) {
                $table->boolean('estado')->default(true)->after('telefono');
            }

            if (! Schema::hasColumn('employees', 'sucursal_id')) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'sucursal_id')) {
                $table->dropColumn('sucursal_id');
            }

            if (Schema::hasColumn('employees', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('employees', 'telefono')) {
                $table->dropColumn('telefono');
            }

            if (Schema::hasColumn('employees', 'primer_apellido')) {
                $table->dropColumn('primer_apellido');
            }

            if (Schema::hasColumn('employees', 'primer_nombre')) {
                $table->dropColumn('primer_nombre');
            }

            if (Schema::hasColumn('employees', 'cedula')) {
                $table->dropColumn('cedula');
            }
        });
    }
};
