<?php

namespace App\Http\Controllers;

use App\Application\UseCases\ManageSportsGear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SportsGearController extends Controller
{
    public function __construct(private readonly ManageSportsGear $sportsGear) {}

    public function categories(): JsonResponse
    {
        return response()->json(['data' => $this->sportsGear->categories()]);
    }

    public function equipmentIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'category_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['sometimes', 'in:active,inactive,all'],
        ]);
        return response()->json(['data' => $this->sportsGear->equipmentList($filters, (string) $request->user()->role)]);
    }

    public function equipmentShow(Request $request, string $id): JsonResponse
    {
        return response()->json(['data' => $this->sportsGear->equipmentFind($id, (string) $request->user()->role)]);
    }

    public function equipmentStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'uuid'],
            'total_units' => ['required', 'integer', 'min:1'],
        ]);
        return response()->json(['data' => $this->sportsGear->equipmentCreate($data)], 201);
    }

    public function equipmentUpdate(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'uuid'],
            'total_units' => ['required', 'integer', 'min:1'],
        ]);
        return response()->json(['data' => $this->sportsGear->equipmentUpdate($id, $data)]);
    }

    public function equipmentDestroy(string $id): JsonResponse
    {
        $this->sportsGear->equipmentDelete($id);
        return response()->json(null, 204);
    }

    public function athletesIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:200'],
            'document_number' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);
        return response()->json(['data' => $this->sportsGear->athletesList($filters)]);
    }

    public function athleteShow(string $id): JsonResponse
    {
        return response()->json(['data' => $this->sportsGear->athleteFind($id)]);
    }

    public function athleteStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:200'],
            'document_number' => ['required', 'string', 'max:30'],
        ]);
        return response()->json(['data' => $this->sportsGear->athleteCreate($data)], 201);
    }

    public function loanStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'athlete_id' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
        return response()->json([
            'data' => $this->sportsGear->loanCreate($data['athlete_id'], (string) $request->user()->id, $data['items']),
        ], 201);
    }

    public function loansIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', 'in:pending,overdue,returned'],
            'athlete_id' => ['sometimes', 'uuid'],
            'user_id' => ['sometimes', 'uuid'],
        ]);
        return response()->json(['data' => $this->sportsGear->loansList($filters)]);
    }

    public function loanShow(string $id): JsonResponse
    {
        return response()->json(['data' => $this->sportsGear->loanFind($id)]);
    }

    public function overdueIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'athlete_id' => ['sometimes', 'uuid'],
        ]);
        return response()->json(['data' => $this->sportsGear->overdueList($filters['athlete_id'] ?? null)]);
    }

    public function returnLoanItem(string $loanId, string $itemId): JsonResponse
    {
        return response()->json(['data' => $this->sportsGear->loanItemReturn($loanId, $itemId)]);
    }

    public function mostRequestedIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
        ]);
        return response()->json(['data' => $this->sportsGear->mostRequested($filters)]);
    }
}
