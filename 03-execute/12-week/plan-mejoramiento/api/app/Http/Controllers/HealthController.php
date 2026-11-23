<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /** Comprueba que la API se encuentra disponible. */
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
