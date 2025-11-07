<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar columna email si no existe
        if (!Schema::hasColumn('administrador', 'email')) {
            Schema::table('administrador', function (Blueprint $table) {
                // quitamos ->after('primer_apellido') para evitar error si no existe
                $table->string('email', 100)->nullable();
            });
        }

        // Agregar columna password si no existe
        if (!Schema::hasColumn('administrador', 'password')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->string('password')->nullable();
            });
        }

        // Agregar remember_token si no existe
        if (!Schema::hasColumn('administrador', 'remember_token')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->rememberToken();
            });
        }

        // Agregar updated_at si no existe
        if (!Schema::hasColumn('administrador', 'updated_at')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }

        // Copiar valores de correo a email si existen
        if (Schema::hasColumn('administrador', 'correo')) {
            DB::statement('UPDATE administrador SET email = correo WHERE correo IS NOT NULL AND (email IS NULL OR email = "")');
        }

        // Asegurar que created_at tenga valores
        if (Schema::hasColumn('administrador', 'created_at')) {
            DB::statement('UPDATE administrador SET created_at = COALESCE(created_at, NOW()) WHERE created_at IS NULL');
        }

        // Asignar correos por defecto si falta email
        DB::statement('UPDATE administrador SET email = CONCAT("admin_", id, "@example.test") WHERE email IS NULL OR email = ""');

        // Forzar que email no sea nulo y sea único
        DB::statement('ALTER TABLE administrador MODIFY email varchar(100) NOT NULL');
        DB::statement('ALTER TABLE administrador ADD UNIQUE KEY administrador_email_unique (email)');
    }

    public function down(): void
    {
        // Quitar índice único
        try {
            DB::statement('ALTER TABLE administrador DROP INDEX administrador_email_unique');
        } catch (\Throwable $th) {
            // Ignorar si no existe
        }

        // Eliminar columnas solo si existen
        Schema::table('administrador', function (Blueprint $table) {
            if (Schema::hasColumn('administrador', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('administrador', 'password')) {
                $table->dropColumn('password');
            }

            if (Schema::hasColumn('administrador', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('administrador', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
