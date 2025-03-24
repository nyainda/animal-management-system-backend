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
use OpenApi\Annotations as OA;

class HealthController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/health",
     *     tags={"Health"},
     *     summary="Get all health records for an animal with filtering and sorting",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="health_status",
     *         in="query",
     *         description="Filter by health status",
     *         required=false,
     *         @OA\Schema(type="string", example="Healthy")
     *     ),
     *     @OA\Parameter(
     *         name="vaccination_status",
     *         in="query",
     *         description="Filter by vaccination status",
     *         required=false,
     *         @OA\Schema(type="string", example="Up-to-date")
     *     ),
     *     @OA\Parameter(
     *         name="vet_contact_id",
     *         in="query",
     *         description="Filter by vet contact ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of health records retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/HealthResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/health?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/health?page=10"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/health?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="path", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/health"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch health records",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to fetch health records"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", example="Internal server error")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
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
            return new ErrorResourceCollection(
                collect([$e->getMessage()]),
                'Failed to fetch health records',
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/animals/{animal}/health",
     *     tags={"Health"},
     *     summary="Create a new health record for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="health_status", type="string", example="Healthy", description="Health status"),
     *             @OA\Property(property="vaccination_status", type="string", example="Up-to-date", description="Vaccination status"),
     *             @OA\Property(property="vet_contact_id", type="string", format="uuid", nullable=true, example="123e4567-e89b-12d3-a456-426614174000", description="Vet contact ID"),
     *             @OA\Property(property="medical_history", type="string", nullable=true, example="Recovered from flu in 2024", description="Medical history"),
     *             @OA\Property(property="dietary_restrictions", type="string", nullable=true, example="No dairy", description="Dietary restrictions"),
     *             @OA\Property(property="neutered_spayed", type="boolean", example=true, description="Neutered or spayed status"),
     *             @OA\Property(property="regular_medication", type="string", nullable=true, example="Daily vitamin supplement", description="Regular medication"),
     *             @OA\Property(property="last_vet_visit", type="string", format="date", nullable=true, example="2025-03-20", description="Last vet visit date"),
     *             @OA\Property(property="insurance_details", type="string", nullable=true, example="Policy #12345", description="Insurance details"),
     *             @OA\Property(property="exercise_requirements", type="string", nullable=true, example="30 min walk daily", description="Exercise requirements"),
     *             @OA\Property(property="parasite_prevention", type="string", nullable=true, example="Monthly flea treatment", description="Parasite prevention"),
     *             @OA\Property(property="vaccinations", type="string", nullable=true, example="Rabies: 2025-01-01", description="Vaccination details"),
     *             @OA\Property(property="allergies", type="string", nullable=true, example="Peanuts", description="Allergies"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Monitor weight", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Health record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/HealthResource"),
     *             @OA\Property(property="message", type="string", example="Health record created successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid health status transition",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid health status transition"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User must be authenticated to create a health record"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create health record",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create health record"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function store(CreateHealthRequest $request, Animal $animal): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return $this->errorResponse('User must be authenticated to create a health record', 401);
            }

            $validatedData = $request->validated();

            if (isset($validatedData['health_status'])) {
                $currentStatus = is_string($animal->status) ? AnimalStatus::tryFrom($animal->status) : null;
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

    /**
     * @OA\Put(
     *     path="/api/animals/{animal}/health/{health}",
     *     tags={"Health"},
     *     summary="Update a specific health record for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="health",
     *         in="path",
     *         required=true,
     *         description="UUID of the health record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="health_status", type="string", example="Sick", description="Updated health status"),
     *             @OA\Property(property="vaccination_status", type="string", example="Due", description="Updated vaccination status"),
     *             @OA\Property(property="vet_contact_id", type="string", format="uuid", nullable=true, example="123e4567-e89b-12d3-a456-426614174000", description="Updated vet contact ID"),
     *             @OA\Property(property="medical_history", type="string", nullable=true, example="Treated for infection in 2025", description="Updated medical history"),
     *             @OA\Property(property="dietary_restrictions", type="string", nullable=true, example="No grains", description="Updated dietary restrictions"),
     *             @OA\Property(property="neutered_spayed", type="boolean", example=false, description="Updated neutered or spayed status"),
     *             @OA\Property(property="regular_medication", type="string", nullable=true, example="Antibiotics", description="Updated regular medication"),
     *             @OA\Property(property="last_vet_visit", type="string", format="date", nullable=true, example="2025-03-25", description="Updated last vet visit date"),
     *             @OA\Property(property="insurance_details", type="string", nullable=true, example="Policy #67890", description="Updated insurance details"),
     *             @OA\Property(property="exercise_requirements", type="string", nullable=true, example="15 min walk daily", description="Updated exercise requirements"),
     *             @OA\Property(property="parasite_prevention", type="string", nullable=true, example="Weekly tick treatment", description="Updated parasite prevention"),
     *             @OA\Property(property="vaccinations", type="string", nullable=true, example="Distemper: 2025-03-01", description="Updated vaccination details"),
     *             @OA\Property(property="allergies", type="string", nullable=true, example="Pollen", description="Updated allergies"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Check temperature", description="Updated notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Health record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/HealthResource"),
     *             @OA\Property(property="message", type="string", example="Health record updated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid health status transition",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid health status transition"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update health record",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update health record"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function update(UpdateHealthRequest $request, Animal $animal, Health $health): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            if (isset($validatedData['health_status'])) {
                $currentStatus = is_string($animal->status) ? AnimalStatus::tryFrom($animal->status) : null;
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
     * @OA\Get(
     *     path="/api/animals/{animal}/health/{health}",
     *     tags={"Health"},
     *     summary="Get a specific health record for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="health",
     *         in="path",
     *         required=true,
     *         description="UUID of the health record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Health record retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/HealthResource"),
     *             @OA\Property(property="message", type="string", example="Health record retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve health record",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to retrieve health record"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/animals/{animal}/health/{health}",
     *     tags={"Health"},
     *     summary="Delete a specific health record for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="health",
     *         in="path",
     *         required=true,
     *         description="UUID of the health record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Health record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Health record deleted successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete health record",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete health record"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/health/reports",
     *     tags={"Health"},
     *     summary="Generate health reports for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Health reports generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status_group", type="string", example="healthy", description="Group of the animal's status"),
     *                 @OA\Property(property="vaccination_due", type="integer", example=2, description="Number of vaccinations due"),
     *                 @OA\Property(
     *                     property="medical_history",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8"),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-03-24T10:00:00Z"),
     *                         @OA\Property(property="history", type="string", example="Recovered from flu")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="recent_vet_visits",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8"),
     *                         @OA\Property(property="visit_date", type="string", format="date", example="2025-03-20"),
     *                         @OA\Property(property="health_status", type="string", example="Healthy"),
     *                         @OA\Property(property="notes", type="string", nullable=true, example="Routine checkup")
     *                     )
     *                 ),
     *                 @OA\Property(property="status_actions", type="string", nullable=true, example="Schedule vet visit", description="Required action based on status")
     *             ),
     *             @OA\Property(property="message", type="string", example="Health reports generated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to generate health reports",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to generate health reports"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function reports(Animal $animal): JsonResponse
    {
        try {
            if (!$animal->exists) {
                return $this->errorResponse('Animal not found', 404);
            }

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

    /**
     * @OA\Post(
     *     path="/api/animals/{animal}/health/action",
     *     tags={"Health"},
     *     summary="Perform a status-based action for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status action performed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="animal_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="action", type="string", example="Schedule vet visit"),
     *                 @OA\Property(property="result", type="string", example="Action performed successfully")
     *             ),
     *             @OA\Property(property="message", type="string", example="Status action performed successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid animal status or no action required",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid animal status"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to perform status action",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to perform status action"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
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

            $result = $this->performActionBasedOnStatus($animal, $action);

            return $this->successResponse($result, 'Status action performed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to perform status action', 500, [$e->getMessage()]);
        }
    }

    private function performActionBasedOnStatus(Animal $animal, string $action): array
    {
        return [
            'animal_id' => $animal->id,
            'action' => $action,
            'result' => 'Action performed successfully'
        ];
    }
}