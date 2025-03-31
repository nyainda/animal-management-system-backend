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
     *         @OA\Schema(type="string", format="uuid")
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ActivityResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
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
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"activity_type", "activity_date"},
     *             @OA\Property(property="activity_type", type="string", example="feeding", description="Type of activity"),
     *             @OA\Property(property="activity_date", type="string", format="date", example="2025-03-30", description="Date of the activity"),
     *             @OA\Property(property="description", type="string", example="Fed the animal with grain", description="Activity description"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Animal seemed healthy", description="Additional notes"),
     *             @OA\Property(property="breeding_date", type="string", format="date", nullable=true, example="2025-03-30", description="Breeding date, if applicable"),
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
            'is_automatic' => false,
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
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="activity",
     *         in="path",
     *         required=true,
     *         description="UUID of the activity",
     *         @OA\Schema(type="string", format="uuid")
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
        // Ensure the activity belongs to the specified animal
        if ($activity->animal_id !== $animal->id) {
            return $this->errorResponse('Activity not found for this animal', 404);
        }

        return $this->successResponse(
            new ActivityResource($activity),
            'Activity retrieved successfully'
        );
    }

    /**
 * Update an existing activity for an animal.
 *
 * @OA\Put(
 *     path="/api/animals/{animal}/activities/{activity}",
 *     tags={"Activities"},
 *     summary="Update an existing activity for an animal",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="animal",
 *         in="path",
 *         required=true,
 *         description="UUID of the animal",
 *         @OA\Schema(type="string", format="uuid", example="9e8fb4fd-72f5-482a-9611-0fa9432939e4")
 *     ),
 *     @OA\Parameter(
 *         name="activity",
 *         in="path",
 *         required=true,
 *         description="UUID of the activity to update",
 *         @OA\Schema(type="string", format="uuid", example="9e8fb502-3e59-4080-989e-236634e840d4")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data to update the activity",
 *         @OA\JsonContent(
 *             required={"activity_type", "activity_date"},
 *             @OA\Property(property="activity_type", type="string", example="feeding", description="Type of activity"),
 *             @OA\Property(property="activity_date", type="string", format="date", example="2025-03-31", description="Date of the activity"),
 *             @OA\Property(property="description", type="string", nullable=true, example="Updated feeding with grain", description="Activity description"),
 *             @OA\Property(property="notes", type="string", nullable=true, example="Animal seemed energetic", description="Additional notes"),
 *             @OA\Property(property="breeding_date", type="string", format="date", nullable=true, example="2025-03-31", description="Breeding date, if applicable"),
 *             @OA\Property(property="breeding_notes", type="string", nullable=true, example="Updated breeding details", description="Breeding-specific notes")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Activity updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", ref="#/components/schemas/ActivityResource", description="Updated activity details"),
 *             @OA\Property(property="message", type="string", example="Activity updated successfully"),
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
 *         description="Forbidden - Cannot edit automatic activities",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cannot edit automatic activities")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Activity or animal not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Activity not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="activity_type", type="array", @OA\Items(type="string", example="The activity type field is required.")),
 *                 @OA\Property(property="activity_date", type="array", @OA\Items(type="string", example="The activity date must be a valid date."))
 *             )
 *         )
 *     )
 * )
 */
public function update(CreateActivityRequest $request, Animal $animal, AnimalActivity $activity)
{
    // Ensure the activity belongs to the specified animal
    if ($activity->animal_id !== $animal->id) {
        return $this->errorResponse('Activity not found for this animal', 404);
    }

    // Prevent editing automatic activities
    if ($activity->is_automatic) {
        return $this->errorResponse('Cannot edit automatic activities', 403);
    }

    // Validate and update the activity
    $validatedData = $request->validated();
    $activity->update($validatedData);

    return $this->successResponse(
        new ActivityResource($activity),
        'Activity updated successfully'
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
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="activity",
     *         in="path",
     *         required=true,
     *         description="UUID of the activity",
     *         @OA\Schema(type="string", format="uuid")
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
        // Ensure the activity belongs to the specified animal
        if ($activity->animal_id !== $animal->id) {
            return $this->errorResponse('Activity not found for this animal', 404);
        }

        if ($activity->is_automatic) {
            return $this->errorResponse('Cannot delete automatic activities', 403);
        }

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
        try {
            $this->activityService->generateBirthdayActivities();
            return $this->successResponse(
                null,
                'Birthday activities generated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate birthday activities', 500);
        }
    }
}