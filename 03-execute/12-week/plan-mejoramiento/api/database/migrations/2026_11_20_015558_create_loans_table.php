<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the loans table.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {

            // UUID v4 of the loan.
            $table->char('id', 36)->primary();

            // Athlete who receives the loan.
            $table->char('athlete_id', 36);

            // Coach who registers the loan.
            $table->char('user_id', 36);

            // UTC date and time when the loan is registered.
            $table->dateTime('loaned_at', 6);

            // Links the loan with its athlete.
            $table->foreign('athlete_id')
                ->references('id')
                ->on('athletes')
                ->restrictOnDelete();

            // Links the loan with the user who registers it.
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            // Speeds up loan queries by athlete and date.
            $table->index(['athlete_id', 'loaned_at']);

            // Speeds up loan queries by user.
            $table->index('user_id');

            // Speeds up queries by registration date.
            $table->index('loaned_at');
        });
    }

    /**
     * Drops the loans table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};