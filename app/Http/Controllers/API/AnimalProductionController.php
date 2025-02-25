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
     * Display a list of production records for an animal
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
            $records,
            'Production records retrieved successfully'
        );
    }

    /**
     * Store a new production record
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
            $result,
            'Production record created successfully',
            201
        );
    }

    /**
     * Display the specified production record
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
            $result,
            'Production record retrieved successfully'
        );
    }

    /**
     * Update the specified production record
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
            $result,
            'Production record updated successfully'
        );
    }

    /**
     * Remove the specified production record
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
     * Get form data for production records
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


    // In routes/api.php


// In AnimalProductionController
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
