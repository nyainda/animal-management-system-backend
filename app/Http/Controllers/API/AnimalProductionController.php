<?php

namespace App\Http\Controllers\API;

use App\Services\FormDataService;
use App\Services\ProductionRecordService;
use App\Models\Animal;
use App\Models\YieldRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Yields\StoreYieldRecordRequest;
use App\Http\Requests\Yields\UpdateYieldRecordRequest;
use App\Traits\ApiResponse;
use App\Http\Resources\Yields\YieldRecordResource;
use OpenApi\Annotations as OA;

class AnimalProductionController extends Controller
{
    use ApiResponse;

    protected $formDataService;
    protected $productionRecordService;

    public function __construct(
        FormDataService $formDataService,
        ProductionRecordService $productionRecordService
    ) {
        $this->formDataService = $formDataService;
        $this->productionRecordService = $productionRecordService;
    }

    /**
     * Display a list of production records for an animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/production",
     *     tags={"Production Records"},
     *     summary="Get production records for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
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
     *         description="List of production records",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/YieldRecordResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/production?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/production?page=10"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/production?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="path", type="string", example="http://api.example.com/api/animals/550e8400-e29b-41d4-a716-446655440000/production"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             ),
     *             @OA\Property(property="message", type="string", example="Production records retrieved successfully"),
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
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
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
    public function index(Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', 403);
        }

        $records = YieldRecord::with([
            'storageLocation',
            'productionMethod',
            'productGrade',
            'productCategory',
            'collector'
        ])
        ->where('animal_id', $animal->id)
        ->orderBy('production_date', 'desc')
        ->orderBy('production_time', 'desc')
        ->paginate(15);

        return $this->successResponse(
            YieldRecordResource::collection($records),
            'Production records retrieved successfully'
        );
    }

    /**
     * Store a new production record.
     *
     * @OA\Post(
     *     path="/api/animals/{animal}/production",
     *     tags={"Production Records"},
     *     summary="Create a new production record for an animal",
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
     *             @OA\Property(
     *                 property="product_category",
     *                 type="object",
     *                 description="Details of the product category",
     *                 @OA\Property(property="name", type="string", example="Milk", description="Category name"),
     *                 @OA\Property(property="description", type="string", example="Fresh dairy product", description="Category description"),
     *                 @OA\Property(property="measurement_unit", type="string", example="Liters", description="Unit of measurement")
     *             ),
     *             @OA\Property(
     *                 property="product_grade",
     *                 type="object",
     *                 description="Details of the product grade",
     *                 @OA\Property(property="name", type="string", example="Grade A", description="Grade name"),
     *                 @OA\Property(property="description", type="string", example="Premium quality", description="Grade description"),
     *                 @OA\Property(property="price_modifier", type="number", format="float", example=1.2, description="Price modifier")
     *             ),
     *             @OA\Property(
     *                 property="production_method",
     *                 type="object",
     *                 description="Details of the production method",
     *                 @OA\Property(property="method_name", type="string", example="Traditional Milking", description="Method name"),
     *                 @OA\Property(property="description", type="string", example="Hand milking technique", description="Method description"),
     *                 @OA\Property(property="requires_certification", type="boolean", example=true, description="Certification requirement"),
     *                 @OA\Property(property="is_active", type="boolean", example=true, description="Active status")
     *             ),
     *             @OA\Property(
     *                 property="collector",
     *                 type="object",
     *                 description="Details of the collector",
     *                 @OA\Property(property="name", type="string", example="John Doe", description="Collector name"),
     *                 @OA\Property(property="contact_info", type="string", example="+1 234 567 8901", description="Collector contact info")
     *             ),
     *             @OA\Property(
     *                 property="storage_location",
     *                 type="object",
     *                 description="Details of the storage location",
     *                 @OA\Property(property="name", type="string", example="Cold Storage 1", description="Storage name"),
     *                 @OA\Property(property="location_code", type="string", example="CS-101", description="Storage location code"),
     *                 @OA\Property(property="description", type="string", example="Primary refrigerated storage", description="Storage description"),
     *                 @OA\Property(
     *                     property="storage_conditions",
     *                     type="array",
     *                     description="List of storage conditions",
     *                     @OA\Items(type="string", example="Temperature controlled")
     *                 ),
     *                 @OA\Property(property="is_active", type="boolean", example=true, description="Active status")
     *             ),
     *             @OA\Property(property="quantity", type="number", format="float", example=100.5, description="Production quantity"),
     *             @OA\Property(property="price_per_unit", type="number", format="float", example=2.50, description="Price per unit"),
     *             @OA\Property(property="total_price", type="number", format="float", example=251.25, description="Total price"),
     *             @OA\Property(property="production_date", type="string", format="date", example="2025-03-12", description="Date of production"),
     *             @OA\Property(property="production_time", type="string", example="14:30", description="Time of production"),
     *             @OA\Property(property="quality_status", type="string", example="Passed", description="Quality status"),
     *             @OA\Property(property="quality_notes", type="string", example="Meets all standard requirements", description="Quality notes"),
     *             @OA\Property(property="trace_number", type="string", example="TR-123456", description="Traceability number"),
     *             @OA\Property(
     *                 property="weather_conditions",
     *                 type="object",
     *                 description="Weather conditions during production",
     *                 @OA\Property(property="temperature", type="number", format="float", example=22.5, description="Temperature in Celsius"),
     *                 @OA\Property(property="humidity", type="integer", example=65, description="Humidity percentage")
     *             ),
     *             @OA\Property(
     *                 property="storage_conditions",
     *                 type="object",
     *                 description="Storage conditions",
     *                 @OA\Property(property="temperature", type="number", format="float", example=4.0, description="Storage temperature in Celsius"),
     *                 @OA\Property(property="humidity", type="integer", example=70, description="Storage humidity percentage")
     *             ),
     *             @OA\Property(property="is_organic", type="boolean", example=true, description="Organic status"),
     *             @OA\Property(property="certification_number", type="string", example="ORG-987654", description="Certification number"),
     *             @OA\Property(
     *                 property="additional_attributes",
     *                 type="object",
     *                 description="Additional product attributes",
     *                 @OA\Property(property="fat_content", type="string", example="3.5%", description="Fat content"),
     *                 @OA\Property(property="pasteurized", type="string", example="Yes", description="Pasteurization status"),
     *                 @OA\Property(property="homogenized", type="string", example="Yes", description="Homogenization status")
     *             ),
     *             @OA\Property(property="notes", type="string", example="Batch processed according to standard protocols.", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Production record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/YieldRecordResource"),
     *             @OA\Property(property="message", type="string", example="Production record created successfully"),
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreYieldRecordRequest $request, Animal $animal)
    {
        $result = $this->productionRecordService->store($request->validated(), $animal);

        if (isset($result->original['error'])) {
            return $this->errorResponse(
                $result->original['error'],
                $result->status()
            );
        }

        return $this->successResponse(
            new YieldRecordResource($result),
            'Production record created successfully',
            201
        );
    }

    /**
     * Display the specified production record.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/production/{production}",
     *     tags={"Production Records"},
     *     summary="Get a specific production record",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="production",
     *         in="path",
     *         required=true,
     *         description="UUID of the production record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Production record retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/YieldRecordResource"),
     *             @OA\Property(property="message", type="string", example="Production record retrieved successfully"),
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Production record or animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Production record not found")
     *         )
     *     )
     * )
     */
    public function show(Animal $animal, YieldRecord $production)
    {
        $result = $this->productionRecordService->show($animal, $production);

        if (isset($result->original['error'])) {
            return $this->errorResponse(
                $result->original['error'],
                $result->status()
            );
        }

        return $this->successResponse(
            new YieldRecordResource($result),
            'Production record retrieved successfully'
        );
    }

    /**
     * Update the specified production record.
     *
     * @OA\Put(
     *     path="/api/animals/{animal}/production/{production}",
     *     tags={"Production Records"},
     *     summary="Update a specific production record",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="production",
     *         in="path",
     *         required=true,
     *         description="UUID of the production record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="production_date", type="string", format="date", example="2025-03-24", description="Date of production"),
     *             @OA\Property(property="production_time", type="string", format="time", example="10:00:00", description="Time of production"),
     *             @OA\Property(property="yield_quantity", type="number", format="float", example=5.5, description="Quantity of yield"),
     *             @OA\Property(property="unit", type="string", example="liters", description="Unit of measurement"),
     *             @OA\Property(property="storage_location_id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8", description="UUID of the storage location"),
     *             @OA\Property(property="production_method_id", type="string", format="uuid", example="7c9e6679-7425-40de-944b-e07fc1f90ae7", description="UUID of the production method"),
     *             @OA\Property(property="product_grade_id", type="string", format="uuid", example="8f14e45f-ceea-41d4-a716-446655440000", description="UUID of the product grade"),
     *             @OA\Property(property="product_category_id", type="string", format="uuid", example="9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d", description="UUID of the product category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Production record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/YieldRecordResource"),
     *             @OA\Property(property="message", type="string", example="Production record updated successfully"),
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateYieldRecordRequest $request, Animal $animal, YieldRecord $production)
    {
        $result = $this->productionRecordService->update($request->validated(), $animal, $production);

        if (isset($result->original['error'])) {
            return $this->errorResponse(
                $result->original['error'],
                $result->status()
            );
        }

        return $this->successResponse(
            new YieldRecordResource($result),
            'Production record updated successfully'
        );
    }

    /**
     * Remove the specified production record.
     *
     * @OA\Delete(
     *     path="/api/animals/{animal}/production/{production}",
     *     tags={"Production Records"},
     *     summary="Delete a specific production record",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="production",
     *         in="path",
     *         required=true,
     *         description="UUID of the production record",
     *         @OA\Schema(type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Production record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Production record deleted successfully"),
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Production record or animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Production record not found")
     *         )
     *     )
     * )
     */
    public function destroy(Animal $animal, YieldRecord $production)
    {
        $result = $this->productionRecordService->destroy($animal, $production);

        if (isset($result->original['error'])) {
            return $this->errorResponse(
                $result->original['error'],
                $result->status()
            );
        }

        return $this->successResponse(
            null,
            'Production record deleted successfully'
        );
    }

    /**
     * Get form data for production records.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/production/form-data",
     *     tags={"Production Records"},
     *     summary="Get form data for creating production records",
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
     *         description="Form data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", description="Form data for production records"),
     *             @OA\Property(property="message", type="string", example="Form data retrieved successfully"),
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
     *         description="Forbidden - User does not own the animal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
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
    public function getFormData(Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', 403);
        }

        $formData = $this->formDataService->getFormData($animal);

        return $this->successResponse(
            $formData,
            'Form data retrieved successfully'
        );
    }

    /**
     * Get production statistics for an animal.
     *
     * @OA\Get(
     *     path="/api/animals/{animal}/production-statistics",
     *     tags={"Production Records"},
     *     summary="Get production statistics for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for statistics (e.g., 'week', 'month', 'year', 'all')",
     *         required=false,
     *         @OA\Schema(type="string", enum={"week", "month", "year", "all"}, default="all")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Production statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", description="Production statistics"),
     *             @OA\Property(property="message", type="string", example="Production statistics retrieved successfully"),
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
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
    public function getProductionStatistics(Animal $animal, Request $request)
    {
        $period = $request->query('period', 'all'); // Default to 'all' if no period is specified

        $statistics = $this->productionRecordService->getProductionStatistics($animal, $period);

        if (isset($statistics->original['error'])) {
            return $this->errorResponse(
                $statistics->original['error'],
                $statistics->status()
            );
        }

        return $this->successResponse(
            $statistics,
            'Production statistics retrieved successfully'
        );
    }
}