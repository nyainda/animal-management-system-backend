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
use Illuminate\Support\Facades\{DB, Log, Cache, Auth};
use Illuminate\Support\Arr;



/**
 * @OA\Info(
 *     title="Animal Management API",
 *     version="1.0.0",
 *     description="API for managing animals, including CRUD operations and detailed animal information.
 *      Workflow: First, register and log in to the system. Then, create an animal to generate a unique animal_id (UUID). Use this animal_id to create tasks, notes, and other related data.",
 *     @OA\Contact(
 *         email="oyugibruce@gmail.com"
 *     )
 * )
 * @OA\Tag(
 *     name="Animals",
 *     description="API endpoints for managing animals"
 * )
 * @OA\Schema(
 *     schema="Animal",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="1"),
 *     @OA\Property(property="name", type="string", example="Bessie"),
 *     @OA\Property(property="type", type="string", example="Cow"),
 *     @OA\Property(property="breed", type="string", example="Holstein"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="tag_number", type="string", example="A123"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2023-01-15"),
 *     @OA\Property(property="gender", type="string", example="female"),
 *     @OA\Property(property="weight", type="number", format="float", example=650.5),
 *     @OA\Property(property="height", type="number", format="float", example=1.4),
 *     @OA\Property(property="is_breeding_stock", type="boolean", example=true),
 *     @OA\Property(property="internal_id", type="string", example="INT-001")
 * )
 * @OA\Schema(
 *     schema="BirthDetail",
 *     type="object",
 *     @OA\Property(property="birth_weight", type="number", format="float", example=40.5),
 *     @OA\Property(property="birth_status", type="string", example="healthy"),
 *     @OA\Property(property="health_at_birth", type="string", example="good")
 * )
 * @OA\Schema(
 *     schema="AnimalWithDetails",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Animal"),
 *         @OA\Schema(ref="#/components/schemas/BirthDetail")
 *     },
 *     @OA\Property(
 *         property="dam",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", example="2"),
 *         @OA\Property(property="name", type="string", example="Daisy"),
 *         @OA\Property(property="type", type="string", example="Cow"),
 *         @OA\Property(property="gender", type="string", example="female"),
 *         @OA\Property(property="birth_date", type="string", format="date", example="2020-05-10")
 *     ),
 *     @OA\Property(
 *         property="sire",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", example="3"),
 *         @OA\Property(property="name", type="string", example="Bullseye"),
 *         @OA\Property(property="type", type="string", example="Bull"),
 *         @OA\Property(property="gender", type="string", example="male"),
 *         @OA\Property(property="birth_date", type="string", format="date", example="2019-08-20")
 *     ),
 *     @OA\Property(
 *         property="offspring",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="string", example="4"),
 *             @OA\Property(property="name", type="string", example="Calfie"),
 *             @OA\Property(property="type", type="string", example="Calf"),
 *             @OA\Property(property="gender", type="string", example="female"),
 *             @OA\Property(property="birth_date", type="string", format="date", example="2024-02-01")
 *         )
 *     ),
 *     @OA\Property(property="status_group", type="string", example="live"),
 *     @OA\Property(property="status_color", type="string", example="#00FF00"),
 *     @OA\Property(property="status_action", type="boolean", example=false)
 * )
 */
class AnimalController extends Controller
{
    use ApiResponse;

    private const CACHE_TTL = 300; // 5 minutes
    private const DEFAULT_PER_PAGE = 15;

    /**
     * @OA\Get(
     *     path="/api/animals",
     *     summary="Get a list of animals",
     *     tags={"Animals"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by animal type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "sold", "deceased"})
     *     ),
     *     @OA\Parameter(
     *         name="breed",
     *         in="query",
     *         description="Filter by breed",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="Filter by gender",
     *         required=false,
     *         @OA\Schema(type="string", enum={"male", "female"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AnimalWithDetails")
     *             ),
     *             @OA\Property(property="message", type="string", example="Animals retrieved successfully"),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to index animal"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->buildAnimalQuery()
                ->forUser($request->user()->id);

            $this->applyFilters($query, $request);

            $cacheKey = $this->generateCacheKey($request);
            $animals = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $request) {
                return $query->paginate($request->input('per_page', self::DEFAULT_PER_PAGE));
            });

            return $this->successResponse($animals, 'Animals retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException('index', $e, ['user_id' => $request->user()->id]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/animals/{id}",
     *     summary="Get a specific animal",
     *     tags={"Animals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Animal ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/AnimalWithDetails"),
     *             @OA\Property(property="message", type="string", example="Animal retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to show animal"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $cacheKey = "animal_{$id}_user_" . Auth::id();
            $animalData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
                $animal = $this->buildAnimalQuery()
                    ->forUser(Auth::id())
                    ->findOrFail($id);

                return $this->formatAnimalResponse($animal);
            });

            return $this->successResponse($animalData, 'Animal retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException('show', $e, ['animal_id' => $id, 'user_id' => Auth::id()]);
        }
    }



    /**
 * @OA\Post(
 *     path="/api/animals",
 *     summary="Create a new animal",
 *     tags={"Animals"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="New Animal"),
 *             @OA\Property(property="type", type="string", example="cattle"),
 *             @OA\Property(property="breed", type="string", example="Angus"),
 *             @OA\Property(property="gender", type="string", example="male", enum={"male", "female"}),
 *             @OA\Property(property="birth_date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="birth_time", type="string", format="date-time", example="2024-01-01 08:00:00"),
 *             @OA\Property(property="birth_status", type="string", example="normal"),
 *             @OA\Property(property="health_at_birth", type="string", example="healthy"),
 *             @OA\Property(
 *                 property="relationships",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="related_animal_id", type="string", example="uuid-of-dam"),
 *                     @OA\Property(property="relationship_type", type="string", example="dam", enum={"dam", "sire"}),
 *                     @OA\Property(property="breeding_date", type="string", format="date", example="2023-04-01"),
 *                     @OA\Property(property="breeding_notes", type="string", example="Natural breeding")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Animal created",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", ref="#/components/schemas/AnimalWithDetails"),
 *             @OA\Property(property="message", type="string", example="Animal created successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Failed to store animal"),
 *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
 *         )
 *     )
 * )
 */

      public function store(StoreAnimalRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $validatedData = $this->prepareValidatedData($request->validated());
                $animal = Animal::create($validatedData);

                $this->handleBirthDetails($animal, $request->validatedBirthDetails());
                $animal->load('birthDetail');

                return $this->successResponse(
                    $this->mergeAnimalWithBirthDetails($animal),
                    'Animal created successfully',
                    201
                );
            });
        } catch (\Exception $e) {
            return $this->handleException('store', $e, [
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/animals/{id}",
     *     summary="Update an existing animal",
     *     tags={"Animals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Animal ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Bessie"),
     *             @OA\Property(property="type", type="string", example="Cow"),
     *             @OA\Property(property="breed", type="string", example="Holstein"),
     *             @OA\Property(property="status", type="string", example="sold"),
     *             @OA\Property(property="tag_number", type="string", example="A123"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="2023-01-15"),
     *             @OA\Property(property="gender", type="string", example="female"),
     *             @OA\Property(property="weight", type="number", format="float", example=650.5),
     *             @OA\Property(property="height", type="number", format="float", example=1.4),
     *             @OA\Property(property="is_breeding_stock", type="boolean", example=true),
     *             @OA\Property(property="internal_id", type="string", example="INT-001"),
     *             @OA\Property(
     *                 property="birth_details",
     *                 type="object",
     *                 @OA\Property(property="birth_weight", type="number", format="float", example=40.5),
     *                 @OA\Property(property="birth_status", type="string", example="healthy"),
     *                 @OA\Property(property="health_at_birth", type="string", example="good")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Animal updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/AnimalWithDetails"),
     *             @OA\Property(property="message", type="string", example="Animal updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update animal"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(UpdateAnimalRequest $request, string $id): JsonResponse
    {
        try {
            $animal = Animal::forUser(Auth::id())->findOrFail($id);

            return DB::transaction(function () use ($request, $animal) {
                $validatedData = $this->prepareValidatedData($request->validated());
                $this->logStatusChange($animal, $validatedData);
                $animal->update($validatedData);

                $this->handleBirthDetails($animal, $request->validatedBirthDetails());
                $animal->refresh();

                return $this->successResponse(
                    $this->formatAnimalResponse($animal),
                    'Animal updated successfully'
                );
            });
        } catch (\Exception $e) {
            return $this->handleException('update', $e, [
                'animal_id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/animals/{id}",
     *     summary="Delete an animal",
     *     tags={"Animals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Animal ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Animal deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Animal deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Animal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Animal not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to destroy animal"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $animal = Animal::forUser(Auth::id())->findOrFail($id);

            DB::transaction(function () use ($animal) {
                optional($animal->birthDetail)->delete();
                $animal->delete();
            });

            Cache::forget("animal_{$id}_user_" . Auth::id());
            return $this->successResponse(null, 'Animal deleted successfully');
        } catch (\Exception $e) {
            return $this->handleException('destroy', $e, ['animal_id' => $id, 'user_id' => Auth::id()]);
        }
    }

    // Private methods remain unchanged...
    private function buildAnimalQuery()
    {
        return Animal::query()
            ->select([
                'id', 'name', 'type', 'breed', 'status',
                'tag_number', 'birth_date', 'gender',
                'weight', 'height', 'is_breeding_stock', 'internal_id'
            ])
            ->with([
                'birthDetail:id,animal_id,birth_weight,birth_status,health_at_birth',
                'damRelationship:id,animal_id,related_animal_id',
                'sireRelationship:id,animal_id,related_animal_id',
                'damRelationship.relatedAnimal:id,name,type,gender,birth_date',
                'sireRelationship.relatedAnimal:id,name,type,gender,birth_date',
            ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $filters = ['type', 'status', 'breed', 'gender'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }
    }

    private function generateCacheKey(Request $request): string
    {
        return sprintf(
            'animals_user_%s_type_%s_status_%s_page_%s_perpage_%s',
            $request->user()->id,
            $request->input('type', 'all'),
            $request->input('status', 'all'),
            $request->input('page', 1),
            $request->input('per_page', self::DEFAULT_PER_PAGE)
        );
    }

    private function prepareValidatedData(array $data): array
    {
        $data['user_id'] = Auth::id();
        if (isset($data['status'])) {
            $status = AnimalStatus::tryFromCaseInsensitive($data['status']);
            $data['status'] = $status ? $status->value : AnimalStatus::ACTIVE->value;
        }
        return $data;
    }

    private function handleBirthDetails(Animal $animal, ?array $birthDetails): void
    {
        if ($birthDetails) {
            $animal->birthDetail()->updateOrCreate(
                ['animal_id' => $animal->id],
                $birthDetails
            );
        }
    }

    private function formatAnimalResponse(Animal $animal): array
    {
        $data = $this->mergeAnimalWithDetails($animal);
        $data['status_group'] = $animal->status->getGroup();
        $data['status_color'] = $animal->status->getStatusColor();
        $data['status_action'] = $animal->status->requiresAction();
        return $data;
    }

    private function mergeAnimalWithBirthDetails(Animal $animal): array
    {
        $animalData = $animal->toArray();
        return array_merge($animalData, Arr::pull($animalData, 'birth_detail', []));
    }

    private function mergeAnimalWithDetails(Animal $animal): array
    {
        $animalData = $this->mergeAnimalWithBirthDetails($animal);
        $animalData['dam'] = optional($animal->dam())->only(['id', 'name', 'type', 'gender', 'birth_date']);
        $animalData['sire'] = optional($animal->sire())->only(['id', 'name', 'type', 'gender', 'birth_date']);
        $animalData['offspring'] = $animal->offspring()->map->only([
            'id', 'name', 'type', 'gender', 'birth_date'
        ]);

        return $animalData;
    }

    private function logStatusChange(Animal $animal, array $data): void
    {
        if (isset($data['status']) && $animal->status->value !== $data['status']) {
            Log::info("Animal Status Transition", [
                'animal_id' => $animal->id,
                'from_status' => $animal->status->value,
                'to_status' => $data['status'],
                'user_id' => Auth::id()
            ]);
        }
    }

    private function handleException(string $method, \Exception $e, array $context): JsonResponse
    {
        DB::rollBack();
        Log::error("Animal {$method} Error", array_merge($context, [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]));

        return $this->errorResponse(
            "Failed to {$method} animal",
            500,
            ['error' => $e->getMessage()]
        );
    }
}