<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de usuarios de Sports Gear.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // Identificador UUID v4 almacenado como cadena de 36 caracteres.
            $table->char('id', 36)->primary();

            // Nombre de usuario único utilizado para iniciar sesión.
            $table->string('username', 120)->unique();

            // Contraseña almacenada mediante un hash Argon2id.
            $table->string('password_hash', 255);

            // Rol del usuario: administrador o entrenador.
            $table->string('role', 20);
        });

        // Restringe los roles a los valores permitidos mediante una condición CHECK.
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT chk_users_role
            CHECK (role IN ('admin', 'coach'))
        ");
    }

    /**
     * Elimina la tabla de usuarios al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
