<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de categorías.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {

            // Identificador UUID v4 almacenado en 36 caracteres.
            $table->char('id', 36)->primary();

            // Nombre único de la categoría.
            $table->string('name', 120)->unique();
        });
    }

    /**
     * Elimina la tabla de categorías al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};