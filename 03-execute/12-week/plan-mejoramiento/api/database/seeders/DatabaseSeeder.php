<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Inicializa los datos necesarios para Sports Gear.
     */
    public function run(): void
    {
        // Obtiene las credenciales del administrador desde la configuración.
        $username = strtolower(trim(config('sports_gear.admin.username', '')));
        $password = config('sports_gear.admin.password');

        // Evita iniciar el sembrado si faltan las credenciales obligatorias.
        if ($username === '' || !is_string($password) || $password === '') {
            throw new RuntimeException(
                'Las credenciales del administrador inicial no están configuradas.'
            );
        }

        DB::transaction(function () use ($username, $password) {

            // Comprueba si el nombre de usuario ya pertenece a otro rol.
            $existingUser = User::where('username', $username)->first();

            if ($existingUser && $existingUser->role !== 'admin') {
                throw new RuntimeException(
                    'El usuario configurado para el administrador ya existe con otro rol.'
                );
            }

            // Define las cinco categorías fijas del sistema.
            $categories = [
                'Balones',
                'Raquetas',
                'Protección',
                'Atletismo',
                'Gimnasio',
            ];

            // Crea únicamente las categorías que todavía no existen.
            foreach ($categories as $categoryName) {
                Category::firstOrCreate([
                    'name' => $categoryName,
                ]);
            }

            // Crea el administrador solamente si todavía no existe.
            // El cast hashed del modelo User protege la contraseña.
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
