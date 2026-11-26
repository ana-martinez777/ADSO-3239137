<?php

namespace Database\Seeders;

use App\Domain\Catalog\Category as DomainCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds the data Sports Gear needs to start.
     */
    public function run(): void
    {
        // Reads the administrator credentials from the configuration.
        $username = strtolower(trim(config('sports_gear.admin.username', '')));
        $password = config('sports_gear.admin.password');

        // Stops the seeding when the required credentials are missing.
        if ($username === '' || !is_string($password) || $password === '') {
            throw new RuntimeException(
                'Las credenciales del administrador inicial no están configuradas.'
            );
        }

        DB::transaction(function () use ($username, $password) {

            // Checks whether the username already belongs to another role.
            $existingUser = User::where('username', $username)->first();

            if ($existingUser && $existingUser->role !== 'admin') {
                throw new RuntimeException(
                    'El usuario configurado para el administrador ya existe con otro rol.'
                );
            }

            // The five fixed categories of the system.
            $categories = DomainCategory::NAMES;

            // Creates only the categories that do not exist yet.
            foreach ($categories as $categoryName) {
                Category::firstOrCreate([
                    'name' => $categoryName,
                ]);
            }

            // Creates the administrator only if it does not exist yet.
            // The hashed cast of the User model protects the password.
            User::firstOrCreate(
                [
                    'username' => $username,
                ],
                [
                    'password_hash' => $password,
                    'role' => 'admin',
                ]
            );
        });
    }
}
