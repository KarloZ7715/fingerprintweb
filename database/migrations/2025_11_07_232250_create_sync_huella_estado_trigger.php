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
        // Crear trigger para sincronizar estados de huella con empleado
        DB::unprepared('
            CREATE TRIGGER sync_huella_estado_on_empleado_update
            AFTER UPDATE ON empleado
            FOR EACH ROW
            BEGIN
                -- Si el empleado pasa a Inactivo, todas sus huellas también
                IF NEW.estado = "Inactivo" AND OLD.estado != "Inactivo" THEN
                    UPDATE huella 
                    SET estado = "Inactiva", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id;
                END IF;
                
                -- Si el empleado pasa a Suspendido, bloquear huellas
                IF NEW.estado = "Suspendido" AND OLD.estado != "Suspendido" THEN
                    UPDATE huella 
                    SET estado = "Bloqueada", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id;
                END IF;
                
                -- Si el empleado vuelve a Activo, reactivar huellas
                IF NEW.estado = "Activo" AND OLD.estado IN ("Inactivo", "Suspendido") THEN
                    UPDATE huella 
                    SET estado = "Activa", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id AND estado IN ("Inactiva", "Bloqueada");
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar trigger
        DB::unprepared('DROP TRIGGER IF EXISTS sync_huella_estado_on_empleado_update');
    }
};
