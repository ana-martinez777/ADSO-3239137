<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that stores the items of each loan.
     */
    public function up(): void
    {
        Schema::create('loan_items', function (Blueprint $table) {

            // UUID v4 of the loan item.
            $table->char('id', 36)->primary();

            // Loan this item belongs to.
            $table->char('loan_id', 36);

            // Equipment that was loaned.
            $table->char('equipment_id', 36);

            // Keeps the reference the equipment had when it was loaned.
            $table->string('equipment_reference', 120);

            // Keeps the category name at the time of the loan.
            $table->string('category_name', 120);

            // Number of units loaned.
            $table->integer('quantity');

            // Deadline to return the units.
            $table->dateTime('due_at', 6);

            // Return date and time; empty until the item is returned.
            $table->dateTime('returned_at', 6)->nullable();

            // The loan this item belongs to.
            $table->foreign('loan_id')
                ->references('id')
                ->on('loans')
                ->restrictOnDelete();

            // Links the item with its equipment.
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->restrictOnDelete();

            // Prevents the same equipment from appearing twice in one loan.
            $table->unique(['loan_id', 'equipment_id']);

            // Speeds up loan queries by equipment.
            $table->index('equipment_id');

            // Speeds up queries on returns and due dates.
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
     * Drops the loan items table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
