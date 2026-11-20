<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla del historial permanente de referencias.
     */
    public function up(): void
    {
        Schema::create('equipment_reference_history', function (Blueprint $table) {

            // Referencia histórica que identifica permanentemente al implemento.
            $table->string('reference', 120)->primary();

            // Identificador del implemento al que pertenece la referencia.
            $table->char('equipment_id', 36);

            // Relaciona la referencia histórica con el implemento correspondiente.
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->restrictOnDelete();

            // Facilita las consultas del historial por identificador del implemento.
            $table->index('equipment_id');
        });
    }

    /**
     * Elimina la tabla del historial al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reference_history');
    }
};