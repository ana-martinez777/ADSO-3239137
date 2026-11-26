<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the permanent reference history table.
     */
    public function up(): void
    {
        Schema::create('equipment_reference_history', function (Blueprint $table) {

            // Historical reference that permanently identifies the equipment.
            $table->string('reference', 120)->primary();

            // Equipment the reference belongs to.
            $table->char('equipment_id', 36);

            // Links the historical reference with its equipment.
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->restrictOnDelete();

            // Speeds up history queries by equipment.
            $table->index('equipment_id');
        });
    }

    /**
     * Drops the history table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reference_history');
    }
};