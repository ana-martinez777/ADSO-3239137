<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the categories table.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {

            // UUID v4 stored in 36 characters.
            $table->char('id', 36)->primary();

            // Unique category name.
            $table->string('name', 120)->unique();
        });
    }

    /**
     * Drops the categories table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};