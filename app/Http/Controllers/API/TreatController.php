<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Treat;
use App\Http\Requests\Treat\StoreTreatRequest;
use App\Http\Requests\Treat\UpdateTreatRequest;
use App\Http\Resources\Treat\TreatResource;
use App\Enums\TreatmentStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\{Auth,Log};
use Symfony\Component\HttpFoundation\Response;

class TreatController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the treatments for a specific animal.
     */
    public function index(Animal $animal): JsonResponse
    {
        $treats = $animal->treats()
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->when(request('from_date'), fn($query) => $query->where('treatment_date', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->where('treatment_date', '<=', request('to_date')))
            ->when(request('type'), fn($query) => $query->where('type', request('type')))
            ->orderBy(request('sort_by', 'treatment_date'), request('sort_direction', 'desc'))
            ->paginate(request('per_page', 15));

        return $this->successResponse(
            TreatResource::collection($treats),
            'Treatments retrieved successfully'
        );
    }

    /**
     * Store a newly created treatment.
     */
    /**
 * Store a newly created treatment.
 */
public function store(StoreTreatRequest $request, Animal $animal): JsonResponse
{
    try {
        DB::beginTransaction();

        // Log the request data for debugging
        Log::info('Treatment store request:', [
            'animal_id' => $animal->id,
            'validated_data' => $request->validated(),
            'user_id' => Auth::id()
        ]);

        $treat = $animal->treats()->create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        DB::commit();

        return $this->successResponse(
            new TreatResource($treat),
            'Treatment created successfully',
            Response::HTTP_CREATED
        );

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return $this->errorResponse(
            'Validation failed',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $e->errors()
        );

    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        return $this->errorResponse(
            'Database error occurred',
            Response::HTTP_BAD_REQUEST,
            [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]
        );

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Treatment creation failed:', [
            'animal_id' => $animal->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return $this->errorResponse(
            'Failed to create treatment',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]
        );
    }
}

    /**
     * Display the specified treatment.
     */
    public function show(Animal $animal, Treat $treat): JsonResponse
    {
        if ($treat->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Treatment not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            new TreatResource($treat),
            'Treatment retrieved successfully'
        );
    }

    /**
     * Update the specified treatment.
     */
    public function update(UpdateTreatRequest $request, Animal $animal, Treat $treat): JsonResponse
    {
        if ($treat->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Treatment not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            DB::beginTransaction();

            $treat->update($request->validated());

            DB::commit();

            return $this->successResponse(
                new TreatResource($treat->fresh()),
                'Treatment updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to update treatment',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified treatment.
     */
    public function destroy(Animal $animal, Treat $treat): JsonResponse
    {
        if ($treat->animal_id !== $animal->id) {
            return $this->errorResponse(
                'Treatment not found for this animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            DB::beginTransaction();

            $treat->delete();

            DB::commit();

            return $this->successResponse(
                null,
                'Treatment deleted successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to delete treatment',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}
