<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedingRecordRequest;
use App\Http\Requests\Feed\UpdateFeedingRecordRequest;
use App\Http\Resources\Feed\FeedingRecordResource;
use App\Models\Animal;
use App\Models\FeedType;
use App\Models\FeedingRecord;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FeedingRecordController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of feeding records for a specific animal.
     */
    public function index(Animal $animal): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        $feedingRecords = $animal->feedingRecords()
            ->with(['feedType', 'feedInventory'])
            ->latest()
            ->paginate(10);

        return $this->successResponse(
            FeedingRecordResource::collection($feedingRecords),
            'Feeding records retrieved successfully'
        );
    }

    /**
     * Store a newly created feeding record for a specific animal and feed type.
     */

    public function store(StoreFeedingRecordRequest $request, Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['animal_id'] = $animal->id;
            $validated['feed_type_id'] = $feedType->id;

            $feedingRecord = FeedingRecord::create($validated);

            return $this->successResponse(
                new FeedingRecordResource($feedingRecord),
                'Feeding record created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create feeding record',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified feeding record.
     */

     public function show(Animal $animal, FeedType $feedType, FeedingRecord $feedingRecord): JsonResponse
{
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    if ($feedingRecord->animal_id !== $animal->id || $feedingRecord->feed_type_id !== $feedType->id) {
        return $this->errorResponse(
            'Feeding record not found for this animal and feed type',
            Response::HTTP_NOT_FOUND
        );
    }

    return $this->successResponse(
        new FeedingRecordResource($feedingRecord->load(['feedType', 'feedInventory'])),
        'Feeding record retrieved successfully'
    );
}


    /**
     * Update the specified feeding record.
     */
    public function update(
        UpdateFeedingRecordRequest $request,
        Animal $animal,
        FeedType $feedType,
        FeedingRecord $feedingRecord
    ): JsonResponse {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($feedingRecord->animal_id !== $animal->id || $feedingRecord->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feeding record not found for this animal and feed type',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();
            // Prevent modification of user_id, animal_id, and feed_type_id
            unset($validated['user_id'], $validated['animal_id'], $validated['feed_type_id']);

            $feedingRecord->update($validated);

            return $this->successResponse(
                new FeedingRecordResource($feedingRecord),
                'Feeding record updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update feeding record',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified feeding record.
     */


    public function destroy(Animal $animal, FeedType $feedType, FeedingRecord $feedingRecord): JsonResponse
    {
        // Check if the animal belongs to the authenticated user
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        // Optional: Check if the feeding record's feed_type matches the route's feed_type
        if ($feedingRecord->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feeding record does not belong to the specified feed type',
                Response::HTTP_NOT_FOUND
            );
        }

        // Check if the feeding record belongs to the animal
        if ($feedingRecord->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Feeding record not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        // Delete the feeding record
        $feedingRecord->delete();

        return $this->successResponse(
            ['message' => 'Feeding record deleted successfully'],
            Response::HTTP_OK
        );
    }
}
