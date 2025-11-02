<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('administrador', 'email')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->string('email', 100)->nullable()->after('primer_apellido');
            });
        }

        if (!Schema::hasColumn('administrador', 'password')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });
        }

        if (!Schema::hasColumn('administrador', 'remember_token')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }

        if (!Schema::hasColumn('administrador', 'updated_at')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }

        DB::statement('UPDATE administrador SET email = correo WHERE correo IS NOT NULL AND (email IS NULL OR email = "")');

        DB::statement('UPDATE administrador SET created_at = COALESCE(created_at, NOW()) WHERE created_at IS NULL');

        DB::statement('UPDATE administrador SET email = CONCAT("admin_", id, "@example.test") WHERE email IS NULL OR email = ""');

        DB::statement('ALTER TABLE administrador MODIFY email varchar(100) NOT NULL');
        DB::statement('ALTER TABLE administrador ADD UNIQUE KEY administrador_email_unique (email)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE administrador DROP INDEX administrador_email_unique');

        if (Schema::hasColumn('administrador', 'email')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        if (Schema::hasColumn('administrador', 'password')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        if (Schema::hasColumn('administrador', 'remember_token')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }

        if (Schema::hasColumn('administrador', 'updated_at')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
