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
        // Respaldar datos existentes si es necesario
        Schema::rename('horario', 'horario_old');

        // Crear nueva tabla horario con estructura mejorada
        Schema::create('horario', function (Blueprint $table) {
            $table->id();

            // Identificación del horario
            $table->string('nombre', 100)->comment('Ej: Turno Mañana, Turno Tarde');
            $table->text('descripcion')->nullable();

            // Horas de trabajo
            $table->time('hora_entrada')->comment('Hora esperada de entrada');
            $table->time('hora_salida')->comment('Hora esperada de salida');

            // Tolerancias en minutos
            $table->integer('tolerancia_entrada')->default(15)
                ->comment('Minutos permitidos de retraso');
            $table->integer('tolerancia_salida')->default(15)
                ->comment('Minutos permitidos de salida temprana');

            // Días laborables (JSON)
            $table->json('dias_laborables')->nullable()
                ->comment('{"lunes": true, "martes": true, ...}');

            // Configuración adicional
            $table->boolean('requiere_entrada')->default(true);
            $table->boolean('requiere_salida')->default(true);
            $table->boolean('activo')->default(true);

            // Sucursal (opcional para horarios específicos)
            $table->unsignedInteger('sucursal_id')->nullable();

            // Timestamps
            $table->timestamps();

            // Índices
            $table->index('sucursal_id', 'idx_horario_sucursal');
            $table->index('activo', 'idx_horario_activo');
        });

        // Insertar horarios por defecto
        DB::table('horario')->insert([
            [
                'nombre' => 'Turno Mañana',
                'descripcion' => 'Personal de 7am a 3pm',
                'hora_entrada' => '07:00:00',
                'hora_salida' => '15:00:00',
                'tolerancia_entrada' => 15,
                'tolerancia_salida' => 15,
                'dias_laborables' => json_encode([
                            'lunes' => true,
                            'martes' => true,
                            'miercoles' => true,
                            'jueves' => true,
                            'viernes' => true,
                            'sabado' => false,
                            'domingo' => false
                        ]),
                'requiere_entrada' => true,
                'requiere_salida' => true,
                'activo' => true,
                'sucursal_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Turno Tarde',
                'descripcion' => 'Personal de 3pm a 11pm',
                'hora_entrada' => '15:00:00',
                'hora_salida' => '23:00:00',
                'tolerancia_entrada' => 10,
                'tolerancia_salida' => 10,
                'dias_laborables' => json_encode([
                            'lunes' => true,
                            'martes' => true,
                            'miercoles' => true,
                            'jueves' => true,
                            'viernes' => true,
                            'sabado' => true,
                            'domingo' => false
                        ]),
                'requiere_entrada' => true,
                'requiere_salida' => true,
                'activo' => true,
                'sucursal_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Administrativo',
                'descripcion' => 'Personal administrativo',
                'hora_entrada' => '08:00:00',
                'hora_salida' => '17:00:00',
                'tolerancia_entrada' => 15,
                'tolerancia_salida' => 30,
                'dias_laborables' => json_encode([
                            'lunes' => true,
                            'martes' => true,
                            'miercoles' => true,
                            'jueves' => true,
                            'viernes' => true,
                            'sabado' => false,
                            'domingo' => false
                        ]),
                'requiere_entrada' => true,
                'requiere_salida' => true,
                'activo' => true,
                'sucursal_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar nueva tabla
        Schema::dropIfExists('horario');

        // Restaurar tabla antigua
        Schema::rename('horario_old', 'horario');
    }
};
