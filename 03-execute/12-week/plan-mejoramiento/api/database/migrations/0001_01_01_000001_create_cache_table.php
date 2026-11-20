<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Migración conservada para mantener el historial inicial de Laravel.
 * Sports Gear utiliza almacenamiento de caché en memoria (array).
 */
return new class extends Migration
{
    /**
     * No se crean tablas porque no utilizamos caché persistente.
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