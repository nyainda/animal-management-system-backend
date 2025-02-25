<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedTypeRequest;
use App\Http\Requests\Feed\UpdateFeedTypeRequest;
use App\Http\Resources\Feed\FeedTypeResource;
use App\Models\Animal;
use App\Models\FeedType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FeedTypeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of feed types for a specific animal.
     */
    public function index(Animal $animal): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        $feedTypes = $animal->feedTypes()
            ->latest()
            ->paginate(10);

        return $this->successResponse(
            FeedTypeResource::collection($feedTypes),
            'Feed types retrieved successfully'
        );
    }

    /**
     * Store a newly created feed type for a specific animal.
     */
    public function store(StoreFeedTypeRequest $request, Animal $animal): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $validated = $request->validated();
            $validated['animal_id'] = $animal->id;
            $validated['user_id'] = Auth::id();  // Add this line to set the user_id

            $feedType = FeedType::create($validated);

            return $this->successResponse(
                new FeedTypeResource($feedType),
                'Feed type created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create feed type',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified feed type.
     */
    public function show(Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($feedType->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Feed type not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            new FeedTypeResource($feedType),
            'Feed type retrieved successfully'
        );
    }

    /**
     * Update the specified feed type.
     */
    public function update(UpdateFeedTypeRequest $request, Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($feedType->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Feed type not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();
            // Prevent modification of animal_id
            unset($validated['animal_id']);

            $feedType->update($validated);

            return $this->successResponse(
                new FeedTypeResource($feedType),
                'Feed type updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update feed type',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified feed type.
     */
    public function destroy(Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($feedType->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Feed type not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $feedType->delete();
            return $this->successResponse(
                null,
                'Feed type deleted successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete feed type',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}
