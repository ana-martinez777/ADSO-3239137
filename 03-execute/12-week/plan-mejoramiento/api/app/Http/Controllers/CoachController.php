<?php
namespace App\Http\Controllers;

use App\Application\UseCases\ListCoaches;
use App\Application\UseCases\RegisterCoach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    public function __construct(
        private readonly RegisterCoach $registerCoach,
        private readonly ListCoaches $listCoaches,
    ) {}

    /** Registra un entrenador; el cliente no puede elegir el rol. */
    public function store(Request $request): JsonResponse
    {
        // El caso de uso asigna siempre el rol coach.
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $coach = $this->registerCoach->execute($data['username'], $data['password']);

        return response()->json([
            'data' => ['id' => $coach->id, 'username' => $coach->username, 'role' => $coach->role],
        ], 201);
    }

    /** Lista entrenadores y permite filtrar por nombre de usuario. */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
        ]);

        $coaches = $this->listCoaches->execute($data['search'] ?? null);

        return response()->json([
            'data' => array_map(
                fn ($coach): array => [
                    'id' => $coach->id,
                    'username' => $coach->username,
                    'role' => $coach->role,
                ],
                $coaches
            ),
        ], 200);
    }
}
