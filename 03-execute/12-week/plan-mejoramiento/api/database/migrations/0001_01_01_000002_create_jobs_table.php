<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Migración conservada para mantener el historial inicial de Laravel.
 * Sports Gear ejecuta las tareas de forma síncrona.
 */
return new class extends Migration
{
    /**
     * No se crean tablas porque no utilizamos colas persistentes.
     */
    public function up(): void
    {
        //
    }

    /**
     * No hay tablas que eliminar al revertir esta migración.
     */
    public function down(): void
    {
        //
    }
};