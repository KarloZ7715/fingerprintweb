<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Agrega el estado 'Pendiente_Huella' al ENUM de empleado.estado
     * para manejar empleados que aún no han registrado su huella dactilar.
     */
    public function up(): void
    {
        // Modificar ENUM del campo estado para incluir 'Pendiente_Huella'
        DB::statement("
            ALTER TABLE empleado 
            MODIFY COLUMN estado ENUM(
                'Activo', 
                'Inactivo', 
                'Suspendido', 
                'Vacaciones', 
                'Pendiente_Huella'
            ) DEFAULT 'Activo'
        ");
    }

    /**
     * Reverse the migrations.
     * 
     * Remueve 'Pendiente_Huella' del ENUM (solo si no hay empleados con ese estado)
     */
    public function down(): void
    {
        // Verificar que no haya empleados en estado Pendiente_Huella
        $count = DB::table('empleado')
            ->where('estado', 'Pendiente_Huella')
            ->count();

        if ($count > 0) {
            throw new \Exception(
                "No se puede revertir la migración: existen {$count} empleado(s) con estado 'Pendiente_Huella'. " .
                "Actualice o elimine estos registros antes de revertir."
            );
        }

        // Revertir ENUM al estado original
        DB::statement("
            ALTER TABLE empleado 
            MODIFY COLUMN estado ENUM(
                'Activo', 
                'Inactivo', 
                'Suspendido', 
                'Vacaciones'
            ) DEFAULT 'Activo'
        ");
    }
};
