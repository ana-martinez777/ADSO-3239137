<?php
namespace App\Http\Controllers;

use App\Application\UseCases\LoginUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly LoginUser $loginUser) {}

    /** Authenticates the user and returns the JWT. */
    public function login(Request $request): JsonResponse
    {
        // Validates the fields before running the use case.
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->loginUser->execute($data['username'], $data['password']);
        $user = $result['user'];

        return response()->json([
            'data' => [
                'access_token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_in' => (int) config('jwt.expiration') * 60,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
            ],
        ], 200);
    }
}
