<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de implementos deportivos.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {

            // Identificador UUID v4 almacenado en 36 caracteres.
            $table->char('id', 36)->primary();

            // Referencia única que identifica cada implemento.
            $table->string('reference', 120)->unique();

            // Identificador de la categoría a la que pertenece.
            $table->char('category_id', 36);

            // Cantidad total de unidades del implemento.
            $table->integer('total_units');

            // Cantidad de unidades que están disponibles.
            $table->integer('available_units');

            // Fecha de eliminación lógica; puede permanecer vacía.
            $table->dateTime('deleted_at', 6)->nullable();

            // Relaciona el implemento con una categoría existente.
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();

            // Facilita las consultas por categoría y referencia.
            $table->index(['category_id', 'reference']);
        });

        // Garantiza que la cantidad total sea como mínimo una unidad.
        DB::statement("
            ALTER TABLE equipment
            ADD CONSTRAINT chk_equipment_total_units
            CHECK (total_units >= 1)
        ");

        // Evita cantidades disponibles negativas.
        DB::statement("
            ALTER TABLE equipment
            ADD CONSTRAINT chk_equipment_available_units_min
            CHECK (available_units >= 0)
        ");

        // Impide que las unidades disponibles superen el total.
        DB::statement("
            ALTER TABLE equipment
            ADD CONSTRAINT chk_equipment_available_units_max
            CHECK (available_units <= total_units)
        ");
    }

    /**
     * Elimina la tabla de implementos al revertir esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
