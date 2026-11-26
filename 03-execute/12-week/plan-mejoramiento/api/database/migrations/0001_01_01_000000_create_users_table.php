<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the Sports Gear users table.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // UUID v4 stored as a 36-character string.
            $table->char('id', 36)->primary();

            // Unique username used to log in.
            $table->string('username', 120)->unique();

            // Password stored as an Argon2id hash.
            $table->string('password_hash', 255);

            // User role: administrator or coach.
            $table->string('role', 20);
        });

        // Restricts the roles to the allowed values with a CHECK constraint.
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT chk_users_role
            CHECK (role IN ('admin', 'coach'))
        ");
    }

    /**
     * Drops the users table when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
