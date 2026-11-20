<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de préstamos.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {

            // Identificador UUID v4 del préstamo.
            $table->char('id', 36)->primary();

            // Identificador del deportista que recibe el préstamo.
            $table->char('athlete_id', 36);

            // Identificador del entrenador que registra el préstamo.
            $table->char('user_id', 36);

            // Fecha y hora en UTC en que se registra el préstamo.
            $table->dateTime('loaned_at', 6);

            // Relaciona el préstamo con el deportista correspondiente.
            $table->foreign('athlete_id')
                ->references('id')
                ->on('athletes')
                ->restrictOnDelete();

            // Relaciona el préstamo con el usuario que lo registra.
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            // Facilita la consulta de préstamos por deportista y fecha.
            $table->index(['athlete_id', 'loaned_at']);

            // Facilita la consulta de préstamos por usuario.
            $table->index('user_id');

            // Facilita la consulta por fecha de registro.
            $table->index('loaned_at');
        });
    }

    /**
     * Elimina la tabla de préstamos al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};