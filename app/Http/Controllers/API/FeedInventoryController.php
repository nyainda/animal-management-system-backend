<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedInventoryRequest;
use App\Http\Requests\Feed\UpdateFeedInventoryRequest;
use App\Http\Resources\Feed\FeedInventoryResource;
use App\Models\Animal;
use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeedInventoryController extends Controller
{
    use ApiResponse;

    /**
     * List feed inventory for a specific animal
     */
    public function index(Animal $animal, Request $request): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        $query = $animal->feedInventories()->with('feedType');

        // Filtering
        if ($request->has('feed_type_id')) {
            $query->where('feed_type_id', $request->input('feed_type_id'));
        }

        // Date range filtering
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('purchase_date', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        // Sorting
        $sortField = $request->input('sort_by', 'purchase_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $feedInventories = $query->paginate($request->input('per_page', 10));

        return $this->successResponse(
            FeedInventoryResource::collection($feedInventories),
            'Feed inventories retrieved successfully'
        );
    }

    /**
     * Store a new feed inventory for a specific animal and feed type
     */
    public function store(StoreFeedInventoryRequest $request, Animal $animal, FeedType $feedType): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['animal_id'] = $animal->id;
            $validated['feed_type_id'] = $feedType->id;

            $feedInventory = FeedInventory::create($validated);

            return $this->successResponse(
                new FeedInventoryResource($feedInventory),
                'Feed inventory created successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create feed inventory',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Show a specific feed inventory
     */
    public function show(Animal $animal, string $feed_type, FeedInventory $feed_inventory): JsonResponse
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($feed_inventory->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Feed inventory not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            new FeedInventoryResource($feed_inventory->load('feedType')),
            'Feed inventory retrieved successfully'
        );
    }

    /**
     * Update a feed inventory
     */
    public function update(
        UpdateFeedInventoryRequest $request,
        Animal $animal,
        FeedType $feedType,
        FeedInventory $feedInventory
    ): JsonResponse {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($feedInventory->animal_id !== $animal->id || $feedInventory->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feed inventory not found',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $validated = $request->validated();

            $feedInventory->update($validated);

            return $this->successResponse(
                new FeedInventoryResource($feedInventory),
                'Feed inventory updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update feed inventory',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Delete a feed inventory
     */
    public function destroy(Animal $animal, FeedType $feedType, FeedInventory $feedInventory): JsonResponse
    {
        // Ensure the user owns the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        // Validate that the feed inventory belongs to the specified animal and feed type
        if ($feedInventory->animal_id !== $animal->id || $feedInventory->feed_type_id !== $feedType->id) {
            return $this->errorResponse(
                'Feed inventory not found for this animal and feed type',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            // Delete the feed inventory
            $feedInventory->delete();

            return $this->successResponse(
                null,
                'Feed inventory deleted successfully',
                Response::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete feed inventory',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get inventory analytics
     */
    public function analytics(Animal $animal): JsonResponse
{
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }

    $now = now();
    $monthStart = $now->copy()->startOfMonth();
    $monthEnd = $now->copy()->endOfMonth();

    $analytics = [
        'financial_metrics' => [
            'total_inventory_value' => number_format($animal->feedInventories()
                ->sum(DB::raw('quantity * unit_price')), 2, '.', ''),
            'monthly_spend' => number_format($animal->feedInventories()
                ->whereBetween('purchase_date', [$monthStart, $monthEnd])
                ->sum(DB::raw('quantity * unit_price')), 2, '.', ''),
            'average_unit_price' => number_format($animal->feedInventories()
                ->avg('unit_price'), 2, '.', ''),
        ],

        'inventory_status' => [
            'total_items' => $animal->feedInventories()->count(),
            'low_stock_items' => $animal->feedInventories()
                ->where('quantity', '<=', 10)
                ->with('feedType')
                ->get(),
            'out_of_stock_items' => $animal->feedInventories()
                ->where('quantity', '=', 0)
                ->with('feedType')
                ->get(),
        ],

        'feed_type_breakdown' => $animal->feedInventories()
            ->select('feed_type_id',
                DB::raw('ROUND(SUM(quantity), 2) as total_quantity'),
                DB::raw('ROUND(SUM(quantity * unit_price), 2) as total_value'),
                DB::raw('ROUND(AVG(unit_price), 2) as average_price'),
                DB::raw('COUNT(*) as purchase_frequency')
            )
            ->groupBy('feed_type_id')
            ->with('feedType')
            ->get(),

        'expiration_analysis' => [
            'expiring_soon' => $animal->feedInventories()
                ->where('expiry_date', '<=', $now->copy()->addDays(30))
                ->where('expiry_date', '>', $now)
                ->with('feedType')
                ->get(),
            'expired' => $animal->feedInventories()
                ->where('expiry_date', '<=', $now)
                ->with('feedType')
                ->get(),
        ],

        'purchase_trends' => $animal->feedInventories()
            ->select(
                DB::raw('TO_CHAR(purchase_date, \'YYYY-MM\') as month'),
                DB::raw('ROUND(SUM(quantity * unit_price), 2) as total_spend'),
                DB::raw('ROUND(SUM(quantity), 2) as total_quantity'),
                DB::raw('COUNT(*) as purchase_count')
            )
            ->groupBy(DB::raw('TO_CHAR(purchase_date, \'YYYY-MM\')'))
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get(),

        'supplier_metrics' => $animal->feedInventories()
            ->select('supplier',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('ROUND(SUM(quantity * unit_price), 2) as total_spend'),
                DB::raw('ROUND(AVG(unit_price), 2) as average_price')
            )
            ->whereNotNull('supplier')
            ->groupBy('supplier')
            ->get(),
    ];

    return $this->successResponse(
        $analytics,
        'Inventory analytics retrieved successfully'
    );
}
}
