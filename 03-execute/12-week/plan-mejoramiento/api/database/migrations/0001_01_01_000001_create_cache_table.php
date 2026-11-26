<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Kept to preserve the initial Laravel migration history.
 * Sports Gear uses in-memory (array) cache storage.
 */
return new class extends Migration
{
    /**
     * No tables are created because persistent cache is not used.
     */
    public function up(): void
    {
        //
    }

    /**
     * There are no tables to drop when this migration is rolled back.
     */
    public function down(): void
    {
        //
    }
};