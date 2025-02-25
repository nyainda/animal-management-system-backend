<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedAnalyticRequest;
use App\Http\Requests\Feed\UpdateFeedAnalyticRequest;
use App\Http\Resources\Feed\FeedAnalyticResource;
use App\Models\Animal;
use App\Models\FeedAnalytic;
use App\Models\FeedType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FeedAnalyticsReportService;

class FeedAnalyticController extends Controller
{
    use ApiResponse;
    protected $reportService;

    public function __construct(FeedAnalyticsReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * List feed analytics for a specific animal
     */
    public function index(Animal $animal, Request $request): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        $query = $animal->feedAnalytics()->with('feedType');

        // Filtering
        if ($request->has('feed_type_id')) {
            $query->where('feed_type_id', $request->input('feed_type_id'));
        }

        // Date range filtering
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('analysis_date', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        // Sorting
        $sortField = $request->input('sort_by', 'analysis_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $feedAnalytics = $query->paginate($request->input('per_page', 10));

        return $this->successResponse(
            FeedAnalyticResource::collection($feedAnalytics),
            'Feed analytics retrieved successfully'
        );
    }

    /**
     * Store a new feed analytic for a specific animal and feed type
     */
    public function store(StoreFeedAnalyticRequest $request, Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['animal_id'] = $animal->id;
            $validated['feed_type_id'] = $feedType->id;

            $feedAnalytic = FeedAnalytic::create($validated);

            return $this->successResponse(
                new FeedAnalyticResource($feedAnalytic),
                'Feed analytic created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create feed analytic',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

   /**
 * Show a specific feed analytic
 */
public function show(Animal $animal, FeedType $feedType, string $feedAnalyticId): JsonResponse
{
    // Check if the authenticated user owns the animal
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    // Retrieve the feed analytic by ID
    $feedAnalytic = FeedAnalytic::where('id', $feedAnalyticId)
        ->where('animal_id', $animal->id)
        ->where('feed_type_id', $feedType->id)
        ->first();

    // Check if the feed analytic exists
    if (!$feedAnalytic) {
        return $this->errorResponse(
            'Feed analytic not found',
            Response::HTTP_NOT_FOUND
        );
    }

    return $this->successResponse(
        new FeedAnalyticResource($feedAnalytic->load('feedType')),
        'Feed analytic retrieved successfully'
    );
}

    /**
     * Update a feed analytic
     */
    public function update(
        UpdateFeedAnalyticRequest $request,
        Animal $animal,
        FeedType $feedType,
        FeedAnalytic $feedAnalytic
    ): JsonResponse {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($feedAnalytic->animal_id !== $animal->id || $feedAnalytic->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feed analytic not found',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();

            $feedAnalytic->update($validated);

            return $this->successResponse(
                new FeedAnalyticResource($feedAnalytic),
                'Feed analytic updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update feed analytic',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Delete a feed analytic
     */
    public function destroy(Animal $animal, FeedType $feedType, FeedAnalytic $feedAnalytic): JsonResponse
    {
        // Ensure the user owns the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        // Validate that the feed analytic belongs to the specified animal and feed type
        if ($feedAnalytic->animal_id !== $animal->id || $feedAnalytic->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feed analytic not found for this animal and feed type',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            // Delete the feed analytic
            $feedAnalytic->delete();

            return $this->successResponse(
                null,
                'Feed analytic deleted successfully',
                Response::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete feed analytic',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }


    /**
     * Generate comprehensive feed analytics report
     */
    public function generateReport(Animal $animal, Request $request): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        try {
            $reportData = $this->reportService->generate($animal);

            return $this->successResponse(
                $reportData,
                'Comprehensive feed analytics report generated'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to generate report',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

}
