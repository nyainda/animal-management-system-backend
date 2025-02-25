<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Health\CreateHealthRequest;
use App\Http\Requests\Health\UpdateHealthRequest;
use App\Http\Resources\HealthResource;
use App\Http\Resources\ErrorResourceCollection;
use App\Models\Animal;
use App\Models\Health;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\{Auth,Log};
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Enums\AnimalStatus;
class HealthController extends Controller
{
    use ApiResponse; // Use the ApiResponse trait for standardized responses

    /**
     * Get all health records for an animal with filtering and sorting
     */
    public function index(Request $request, Animal $animal): ResourceCollection
    {
        try {
            $healthRecords = $animal->healthRecords()
                ->when($request->health_status, fn($query, $status) => $query->where('health_status', $status))
                ->when($request->vaccination_status, fn($query, $status) => $query->where('vaccination_status', $status))
                ->when($request->vet_contact_id, fn($query, $vetId) => $query->where('vet_contact_id', $vetId))
                ->when($request->sort_by, fn($query, $sortBy) => $query->orderBy($sortBy, $request->sort_order ?? 'asc'))
                ->paginate($request->per_page ?? 15);

            return HealthResource::collection($healthRecords);
        } catch (\Exception $e) {
            // Return an ErrorResourceCollection for error responses
            return new ErrorResourceCollection(
                collect([$e->getMessage()]),
                'Failed to fetch health records',
                500
            );
        }
    }

    /**
     * Create a new health record for an animal
     */
    public function store(CreateHealthRequest $request, Animal $animal): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return $this->errorResponse('User must be authenticated to create a health record', 401);
            }

            $validatedData = $request->validated();

            // Check if the health status transition is valid
            if (isset($validatedData['health_status'])) {
                // Ensure $animal->status is a string
                $currentStatus = is_string($animal->status) ? AnimalStatus::tryFrom($animal->status) : null;
                // Ensure $validatedData['health_status'] is a string
                $newStatus = is_string($validatedData['health_status']) ? AnimalStatus::tryFrom($validatedData['health_status']) : null;

                if ($currentStatus && $newStatus && !$currentStatus->canTransitionTo($newStatus)) {
                    return $this->errorResponse('Invalid health status transition', 400);
                }
            }

            $healthRecord = $animal->healthRecords()->create([
                ...$validatedData,
                'user_id' => $userId,
                'id' => Str::uuid()
            ]);

            return $this->successResponse(
                new HealthResource($healthRecord),
                'Health record created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create health record', 500, [$e->getMessage()]);
        }
    }

    public function update(UpdateHealthRequest $request, Animal $animal, Health $health): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Check if the health status transition is valid
            if (isset($validatedData['health_status'])) {
                // Ensure $animal->status is a string
                $currentStatus = is_string($animal->status) ? AnimalStatus::tryFrom($animal->status) : null;
                // Ensure $validatedData['health_status'] is a string
                $newStatus = is_string($validatedData['health_status']) ? AnimalStatus::tryFrom($validatedData['health_status']) : null;

                if ($currentStatus && $newStatus && !$currentStatus->canTransitionTo($newStatus)) {
                    return $this->errorResponse('Invalid health status transition', 400);
                }
            }

            $health->update($validatedData);

            return $this->successResponse(
                new HealthResource($health),
                'Health record updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update health record', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get a specific health record for an animal
     */
    public function show(Animal $animal, Health $health): JsonResponse
    {
        try {
            return $this->successResponse(
                new HealthResource($health),
                'Health record retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve health record', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update a specific health record for an animal
     */


    /**
     * Delete a specific health record for an animal
     */
    public function destroy(Animal $animal, Health $health): JsonResponse
    {
        try {
            $health->delete();

            return $this->successResponse(
                null,
                'Health record deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete health record', 500, [$e->getMessage()]);
        }
    }


public function reports(Animal $animal): JsonResponse
{
    try {
        if (!$animal->exists) {
            return $this->errorResponse('Animal not found', 404);
        }

        // Ensure $animal->status is a string before calling tryFrom
        $animalStatus = is_string($animal->status) ? AnimalStatus::tryFrom($animal->status) : null;
        $statusGroup = $animalStatus ? $animalStatus->getGroup() : 'other';

        $reports = [
            'status_group' => $statusGroup,
            'vaccination_due' => $animal->healthRecords()
                ->where('vaccination_status', 'Due')
                ->count(),
            'medical_history' => $animal->healthRecords()
                ->whereNotNull('medical_history')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->created_at,
                        'history' => $record->medical_history
                    ];
                }),
            'recent_vet_visits' => $animal->healthRecords()
                ->whereNotNull('last_vet_visit')
                ->orderBy('last_vet_visit', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'visit_date' => $record->last_vet_visit,
                        'health_status' => $record->health_status,
                        'notes' => $record->notes
                    ];
                }),
            'status_actions' => $animalStatus ? $animalStatus->requiresAction() : null
        ];

        return $this->successResponse($reports, 'Health reports generated successfully');
    } catch (\Exception $e) {
        Log::error('Failed to generate health reports: ' . $e->getMessage(), [
            'animal_id' => $animal->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return $this->errorResponse('Failed to generate health reports', 500, [$e->getMessage()]);
    }
}

public function performStatusAction(Animal $animal): JsonResponse
{
    try {
        $animalStatus = AnimalStatus::tryFrom($animal->status);
        if (!$animalStatus) {
            return $this->errorResponse('Invalid animal status', 400);
        }

        $action = $animalStatus->requiresAction();
        if (!$action) {
            return $this->errorResponse('No action required for the current status', 400);
        }

        // Perform the required action (e.g., schedule a vet visit, adjust nutrition plan, etc.)
        // This is a placeholder for the actual logic
        $result = $this->performActionBasedOnStatus($animal, $action);

        return $this->successResponse($result, 'Status action performed successfully');
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to perform status action', 500, [$e->getMessage()]);
    }
}

private function performActionBasedOnStatus(Animal $animal, string $action): array
{
    // Implement the logic to perform the action based on the status
    // This is a placeholder for the actual implementation
    return [
        'animal_id' => $animal->id,
        'action' => $action,
        'result' => 'Action performed successfully'
    ];
}
}
