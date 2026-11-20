<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla que almacena los detalles de cada préstamo.
     */
    public function up(): void
    {
        Schema::create('loan_items', function (Blueprint $table) {

            // Identificador UUID v4 del detalle del préstamo.
            $table->char('id', 36)->primary();

            // Identificador del préstamo al que pertenece este detalle.
            $table->char('loan_id', 36);

            // Identificador del implemento deportivo prestado.
            $table->char('equipment_id', 36);

            // Conserva la referencia que tenía el implemento al prestarse.
            $table->string('equipment_reference', 120);

            // Conserva el nombre de la categoría al momento del préstamo.
            $table->string('category_name', 120);

            // Cantidad de unidades prestadas.
            $table->integer('quantity');

            // Fecha y hora límite para devolver las unidades.
            $table->dateTime('due_at', 6);

            // Fecha y hora de devolución; permanece vacía mientras no se devuelva.
            $table->dateTime('returned_at', 6)->nullable();

            // Relaciona el detalle con su préstamo.
            $table->foreign('loan_id')
                ->references('id')
                ->on('loans')
                ->restrictOnDelete();

            // Relaciona el detalle con el implemento correspondiente.
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->restrictOnDelete();

            // Evita registrar el mismo implemento dos veces en un préstamo.
            $table->unique(['loan_id', 'equipment_id']);

            // Facilita las consultas de préstamos por implemento.
            $table->index('equipment_id');

            // Facilita las consultas de devoluciones y vencimientos.
            $table->index(['returned_at', 'due_at']);
        });

        // Impide registrar cantidades iguales o inferiores a cero.
        DB::statement("
            ALTER TABLE loan_items
            ADD CONSTRAINT chk_loan_items_quantity
            CHECK (quantity > 0)
        ");
    }

    /**
     * Elimina la tabla de detalles de préstamos al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
