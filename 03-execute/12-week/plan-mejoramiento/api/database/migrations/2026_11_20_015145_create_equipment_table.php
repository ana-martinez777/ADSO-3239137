<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the sports equipment table.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {

            // UUID v4 stored in 36 characters.
            $table->char('id', 36)->primary();

            // Unique reference that identifies each piece of equipment.
            $table->string('reference', 120)->unique();

            // Category the equipment belongs to.
            $table->char('category_id', 36);

            // Total units of the equipment.
            $table->integer('total_units');

            // Units currently available.
            $table->integer('available_units');

            // Soft-delete date; may stay empty.
            $table->dateTime('deleted_at', 6)->nullable();

            // Links the equipment with an existing category.
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();

            // Speeds up queries by category and reference.
            $table->index(['category_id', 'reference']);
        });

        // Ensures the total is at least one unit.
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

        // Prevents available units from exceeding the total.
        DB::statement("
            ALTER TABLE equipment
            ADD CONSTRAINT chk_equipment_available_units_max
            CHECK (available_units <= total_units)
        ");
    }

    /**
     * Drops the equipment table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
