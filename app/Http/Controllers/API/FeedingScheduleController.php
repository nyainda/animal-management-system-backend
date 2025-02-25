<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedingScheduleRequest;
use App\Http\Requests\Feed\UpdateFeedingScheduleRequest;
use App\Http\Resources\Feed\FeedingScheduleResource;
use App\Models\Animal;
use App\Models\FeedType;
use App\Models\FeedingSchedule;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Log};
use Symfony\Component\HttpFoundation\Response;

class FeedingScheduleController extends Controller
{
    use ApiResponse;

    /**
     * List feeding schedules for a specific animal
     */
    public function index(Animal $animal, Request $request): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        $query = $animal->feedingSchedules()->with('feedType');

        // Filtering
        if ($request->has('feed_type_id')) {
            $query->where('feed_type_id', $request->input('feed_type_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $feedingSchedules = $query->paginate($request->input('per_page', 10));

        return $this->successResponse(
            FeedingScheduleResource::collection($feedingSchedules),
            'Feeding schedules retrieved successfully'
        );
    }

    /**
     * Store a new feeding schedule for a specific animal and feed type
     */
    public function store(StoreFeedingScheduleRequest $request, Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['animal_id'] = $animal->id;
            $validated['feed_type_id'] = $feedType->id;
            $validated['is_active'] = $validated['is_active'] ?? true;

            $feedingSchedule = FeedingSchedule::create($validated);

            return $this->successResponse(
                new FeedingScheduleResource($feedingSchedule),
                'Feeding schedule created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create feeding schedule',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Show a specific feeding schedule
     */
    /**
 * Show a specific feeding schedule
 */
public function show(Animal $animal, FeedType $feedType, FeedingSchedule $feedingSchedule): JsonResponse
{
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    if ($feedingSchedule->animal_id !== $animal->id || $feedingSchedule->feed_type_id !== $feedType->id) {
        return $this->errorResponse(
            'Feeding schedule not found for this animal and feed type',
            Response::HTTP_NOT_FOUND
        );
    }

    return $this->successResponse(
        new FeedingScheduleResource($feedingSchedule),
        'Feeding schedule retrieved successfully'
    );
}

    /**
     * Update a feeding schedule
     */
    public function update(
        UpdateFeedingScheduleRequest $request,
        Animal $animal,
        FeedType $feedType,
        FeedingSchedule $feedingSchedule
    ): JsonResponse {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($feedingSchedule->animal_id !== $animal->id || $feedingSchedule->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feeding schedule not found',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();

            $feedingSchedule->update($validated);

            return $this->successResponse(
                new FeedingScheduleResource($feedingSchedule),
                'Feeding schedule updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update feeding schedule',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Delete a feeding schedule
     */
    public function destroy(Animal $animal, FeedType $feedType, FeedingSchedule $feedingSchedule): JsonResponse
{
    // Ensure the user owns the animal
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    // Validate that the feeding schedule belongs to the specified animal and feed type
    if ($feedingSchedule->animal_id !== $animal->id || $feedingSchedule->feed_type_id !== $feedType->id) {
        return $this->errorResponse(
            'Feeding schedule not found for this animal and feed type',
            Response::HTTP_NOT_FOUND
        );
    }

    try {
        // Delete the feeding schedule
        $feedingSchedule->delete();

        return $this->successResponse(
            null,
            'Feeding schedule deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    } catch (\Exception $e) {
        return $this->errorResponse(
            'Failed to delete feeding schedule',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [$e->getMessage()]
        );
    }
}

    /**
     * Generate upcoming feeding schedule report
     */
    public function upcomingSchedule(Animal $animal, Request $request): JsonResponse
{
    // Ensure the user has permission to view this animal's schedule
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    try {
        // Validate input and set default value for days
        $days = max(1, (int)$request->input('days', 7));

        // Fetch active feeding schedules with feed type details
        $feedingSchedules = $animal->feedingSchedules()
            ->where('is_active', true)
            ->with('feedType')
            ->get();

        // Calculate upcoming feeding times for each schedule
        $upcomingSchedules = $feedingSchedules->flatMap(function ($schedule) use ($days) {
            return collect($schedule->calculateNextFeedingTimes($days))
                ->map(function ($time) use ($schedule) {
                    return [
                        'schedule_id' => $schedule->id,
                        'feed_type' => $schedule->feedType->name ?? 'Unknown',
                        'feeding_time' => $time,
                        'portion_size' => $schedule->portion_size,
                        'portion_unit' => $schedule->portion_unit,
                        'special_instructions' => $schedule->special_instructions,
                    ];
                });
        })
        ->sortBy('feeding_time') // Sort by feeding time
        ->values(); // Reset keys

        return $this->successResponse(
            $upcomingSchedules,
            'Upcoming feeding schedules retrieved successfully'
        );
    } catch (\Exception $e) {
        Log::error('Error retrieving upcoming feeding schedules: ' . $e->getMessage());
        return $this->errorResponse(
            'Failed to retrieve upcoming feeding schedules',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [$e->getMessage()]
        );
    }
}
}
