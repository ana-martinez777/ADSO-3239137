<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Kept to preserve the initial Laravel migration history.
 * Sports Gear runs jobs synchronously.
 */
return new class extends Migration
{
    /**
     * No tables are created because persistent queues are not used.
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