<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the athletes table.
     */
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {

            // UUID v4 stored in 36 characters.
            $table->char('id', 36)->primary();

            // Athlete's full name.
            $table->string('full_name', 200);

            // Unique document number of each athlete.
            $table->string('document_number', 30)->unique();
        });
    }

    /**
     * Drops the athletes table when the migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
