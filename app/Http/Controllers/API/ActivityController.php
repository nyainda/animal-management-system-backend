<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CreateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Animal;
use App\Models\AnimalActivity;
use App\Services\AnimalActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use OpenApi\Annotations as OA;

class ActivityController extends Controller
{
    use ApiResponse;

    protected $activityService;

    /**
     * Create a new controller instance.
     *
     * @param AnimalActivityService $activityService
     */
    public function __construct(AnimalActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get activities for an animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/activities",
     *     tags={"Activities"},
     *     summary="Get activities for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by activity type",
     *         required=false,
     *         @OA\Schema(type="string", example="feeding")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter activities from this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter activities up to this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of activities per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of activities",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedActivityResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found")
     *         )
     *     )
     * )
     */
    public function index(Request $request, Animal $animal): ResourceCollection
    {
        $activities = $animal->activities()
            ->when($request->type, fn($query, $type) => $query->where('activity_type', $type))
            ->when($request->from_date, fn($query, $date) => $query->whereDate('activity_date', '>=', $date))
            ->when($request->to_date, fn($query, $date) => $query->whereDate('activity_date', '<=', $date))
            ->latest('activity_date')
            ->paginate($request->per_page ?? 15);

        return ActivityResource::collection($activities);
    }

    /**
     * Create a new activity for an animal.
     *
     * @OA\Post(
     *     path="/api/animals/{animal}/activities",
     *     tags={"Activities"},
     *     summary="Create a new activity for an animal",
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
     *             @OA\Property(property="activity_type", type="string", example="feeding", description="Type of activity"),
     *             @OA\Property(property="activity_date", type="string", format="date", example="2025-03-24", description="Date of the activity"),
     *             @OA\Property(property="description", type="string", example="Fed the animal with grain", description="Activity description"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Animal seemed healthy", description="Additional notes"),
     *             @OA\Property(property="breeding_date", type="string", format="date", nullable=true, example="2025-03-24", description="Breeding date, if applicable"),
     *             @OA\Property(property="breeding_notes", type="string", nullable=true, example="Successful breeding", description="Breeding-specific notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Activity created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ActivityResource"),
     *             @OA\Property(property="message", type="string", example="Activity created successfully"),
     *             @OA\Property(property="status", type="string", example="success")
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
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found")
     *         )
     *     )
     * )
     */
    public function store(CreateActivityRequest $request, Animal $animal)
    {
        $validatedData = $request->validated();
        $activity = $animal->activities()->create([
            ...$validatedData,
            'user_id' => Auth::id(),
            'is_automatic' => false
        ]);

        return $this->successResponse(
            new ActivityResource($activity),
            'Activity created successfully',
            201
        );
    }

    /**
     * Get a specific activity for an animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/activities/{activity}",
     *     tags={"Activities"},
     *     summary="Get a specific activity",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="activity",
     *         in="path",
     *         required=true,
     *         description="UUID of the activity",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ActivityResource"),
     *             @OA\Property(property="message", type="string", example="Activity retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
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
     *         response=404,
     *         description="Activity or animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Activity not found")
     *         )
     *     )
     * )
     */
    public function show(Animal $animal, AnimalActivity $activity)
    {
        return $this->successResponse(
            new ActivityResource($activity),
            'Activity retrieved successfully'
        );
    }

    /**
     * Delete an activity for an animal.
     *
     * @OA\Delete(
     *     path="/api/animals/{animal}/activities/{activity}",
     *     tags={"Activities"},
     *     summary="Delete a specific activity",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="activity",
     *         in="path",
     *         required=true,
     *         description="UUID of the activity",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Activity deleted successfully"),
     *             @OA\Property(property="status", type="string", example="success")
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
     *         response=403,
     *         description="Forbidden - Cannot delete automatic activities",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete automatic activities")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity or animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Activity not found")
     *         )
     *     )
     * )
     */
    public function destroy(Animal $animal, AnimalActivity $activity)
    {
        abort_if($activity->is_automatic, 403, 'Cannot delete automatic activities');

        $activity->delete();

        return $this->successResponse(
            null,
            'Activity deleted successfully'
        );
    }

    /**
     * Generate birthday activities manually.
     *
     * @OA\Post(
     *     path="/api/activities/generate-birthdays",
     *     tags={"Activities"},
     *     summary="Generate birthday activities for animals",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Birthday activities generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Birthday activities generated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
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
     *         response=500,
     *         description="Failed to generate birthday activities",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to generate birthday activities")
     *         )
     *     )
     * )
     */
    public function generateBirthdayActivities()
    {
        $this->activityService->generateBirthdayActivities();

        return $this->successResponse(
            null,
            'Birthday activities generated successfully'
        );
    }
}