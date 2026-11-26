<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /** Checks that the API is available. */
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
