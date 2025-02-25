<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Animal\{
    StoreAnimalRequest,
    UpdateAnimalRequest
};
use App\Models\Animal;
use App\Traits\ApiResponse;
use App\Enums\AnimalStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Log,Cache, Auth};
use Illuminate\Support\Arr;
use Exception;

class AnimalController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of animals.
     */
    public function index(Request $request): JsonResponse
{
    try {
        $perPage = $request->input('per_page', 15);

        $query = Animal::query()
            ->select([
                'id', 'name', 'type', 'breed', 'status',
                'tag_number', 'birth_date', 'gender',
                'weight', 'height', 'is_breeding_stock'
            ])
            ->with([
                'birthDetail:id,animal_id,birth_weight,birth_status,health_at_birth',
                'damRelationship:id,animal_id,related_animal_id',
                'sireRelationship:id,animal_id,related_animal_id',
                'damRelationship.relatedAnimal:id,name,type,gender,birth_date',
                'sireRelationship.relatedAnimal:id,name,type,gender,birth_date',
            ])
            ->forUser($request->user()->id);

        // Add query filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Cache results for 5 minutes
        $cacheKey = sprintf(
            'animals_user_%s_type_%s_status_%s_page_%s',
            $request->user()->id,
            $request->input('type', 'all'),
            $request->input('status', 'all'),
            $request->input('page', 1)
        );

        $animals = Cache::remember($cacheKey, 60, function() use ($query, $perPage) {
            return $query->paginate($perPage);
        });

        return $this->successResponse($animals, 'Animals retrieved successfully');
    } catch (Exception $e) {
        Log::error('Animal Index Error', [
            'user_id' => $request->user()->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return $this->errorResponse('Failed to retrieve animals', 500, [$e->getMessage()]);
    }
}

    /**
     * Store a newly created animal and its birth details.
     */
    public function store(StoreAnimalRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate and prepare data
            $validatedData = $request->validated();
            $status = AnimalStatus::tryFrom($validatedData['status'] ?? AnimalStatus::ACTIVE->value);
            $validatedData['status'] = $status->value;

            // Log creation attempt
            Log::info('Animal Creation Attempt', [
                'user_id' => $request->user()->id,
                'validated_data' => $validatedData
            ]);

            // Create animal record
            $animal = Animal::create([
                ...$validatedData,
                'user_id' => $request->user()->id
            ]);

            if (!$animal) {
                throw new \Exception('Failed to create animal record');
            }

            // Create birth details
            $birthDetails = $request->validatedBirthDetails();
            $animal->birthDetail()->create($birthDetails);

            DB::commit();

            // Load and merge data
            $animal->load('birthDetail');
            $responseData = $this->mergeAnimalWithBirthDetails($animal);

            return $this->successResponse(
                $responseData,
                'Animal created successfully',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Animal Creation Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()->id ?? 'Unknown',
                'request_data' => $request->all()
            ]);
            return $this->errorResponse(
                'Failed to create animal',
                500,
                [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );
        }
    }

    /**
     * Update the specified animal and its birth details.
     */
    public function update(UpdateAnimalRequest $request, string $id): JsonResponse
{
    try {
        $animal = Animal::forUser(Auth::id())->findOrFail($id);

        DB::beginTransaction();

        $validatedData = $request->validated();

        // Handle status transition with simplified logic
        if (isset($validatedData['status'])) {
            $currentStatus = $animal->status;
            $newStatus = AnimalStatus::tryFromCaseInsensitive($validatedData['status']);

            if (!$newStatus) {
                throw new \InvalidArgumentException("Invalid status: {$validatedData['status']}");
            }

            $validatedData['status'] = $newStatus->value;

            // Log status change if different
            if ($currentStatus !== $newStatus) {
                Log::info("Animal Status Transition", [
                    'animal_id' => $animal->id,
                    'from_status' => $currentStatus->value,
                    'to_status' => $newStatus->value,
                    'user_id' => Auth::id()
                ]);
            }
        }

        $animal->update($validatedData);

        // Update birth details if provided
        if ($birthDetails = $request->validatedBirthDetails()) {
            $animal->birthDetail()->updateOrCreate(
                ['animal_id' => $animal->id],
                $birthDetails
            );
        }

        $animal->refresh();

        DB::commit();

        $responseData = $this->mergeAnimalWithDetails($animal);
        $responseData['status_group'] = $animal->status->getGroup();
        $responseData['status_color'] = $animal->status->getStatusColor();
        $responseData['status_action'] = $animal->status->requiresAction();

        return $this->successResponse(
            $responseData,
            'Animal updated successfully'
        );
    } catch (Exception $e) {
        DB::rollBack();

        Log::error('Animal Update Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'animal_id' => $id,
            'user_id' => Auth::id(),
            'request_data' => $request->validated()
        ]);

        return $this->errorResponse('Failed to update animal', 500, [
            'error' => $e->getMessage()
        ]);
    }
}

     /**
     * Remove the specified animal and its birth details.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $animal = Animal::forUser(Auth::id())->findOrFail($id);

            DB::beginTransaction();

            $animal->birthDetail?->delete();
            $animal->delete();

            DB::commit();

            return $this->successResponse(null, 'Animal deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete animal', 500, [$e->getMessage()]);
        }
    }

    /**
     * Merge animal and birth details into a single array.
     */
    private function mergeAnimalWithBirthDetails(Animal $animal): array
    {
        $animalData = $animal->toArray();
        $birthDetail = Arr::pull($animalData, 'birth_detail', []);

        return array_merge($animalData, $birthDetail);
    }

    /**
     * Merge animal with additional details and relationships.
     */
    private function mergeAnimalWithDetails(Animal $animal): array
    {
        $animalData = $this->mergeAnimalWithBirthDetails($animal);

        $animalData['dam'] = $animal->dam() ? $animal->dam()->only(['id', 'name', 'type', 'gender', 'birth_date']) : null;
        $animalData['sire'] = $animal->sire() ? $animal->sire()->only(['id', 'name', 'type', 'gender', 'birth_date']) : null;
        $animalData['offspring'] = $animal->offspring()->map->only([
            'id', 'name', 'type', 'gender', 'birth_date'
        ]);

        return $animalData;
    }
}
