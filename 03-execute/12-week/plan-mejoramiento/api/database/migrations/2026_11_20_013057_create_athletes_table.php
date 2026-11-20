<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de deportistas.
     */
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {

            // Identificador UUID v4 almacenado en 36 caracteres.
            $table->char('id', 36)->primary();

            // Nombre completo del deportista.
            $table->string('full_name', 200);

            // Número de documento único para cada deportista.
            $table->string('document_number', 30)->unique();
        });
    }

    /**
     * Elimina la tabla de deportistas al revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
