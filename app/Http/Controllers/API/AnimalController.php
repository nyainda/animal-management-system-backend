<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Animal\{StoreAnimalRequest, UpdateAnimalRequest};
use App\Models\Animal;
use App\Services\AnimalService;
use App\Traits\ApiResponse;
use App\Enums\AnimalStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cache, DB, Log};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

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

    private const CACHE_TTL = 30;
    private const DEFAULT_PER_PAGE = 15;
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly AnimalService $animalService
    ) {}

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
     *         @OA\Schema(type="integer", default=15, maximum=100)
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or tag number",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid parameters"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to retrieve animals"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
{
    $this->validateIndexRequest($request);

    $userId = Auth::id();
    $cacheKey = $this->generateCacheKey($request, $userId);

    $animals = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request, $userId) {
        return $this->animalService->getPaginatedAnimals($request, $userId);
    });

    return $this->successResponse($animals, 'Animals retrieved successfully');
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
     *             @OA\Property(property="message", type="string", example="Failed to retrieve animal"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $userId = Auth::id();
        $cacheKey = "animal_{$id}_user_{$userId}";

        $animalData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id, $userId) {
            return $this->animalService->getAnimalWithDetails($id, $userId);
        });

        return $this->successResponse($animalData, 'Animal retrieved successfully');
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
     *             @OA\Property(property="message", type="string", example="Failed to create animal"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(StoreAnimalRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $animalData = $this->animalService->createAnimal($request);

            return $this->successResponse(
                $animalData,
                'Animal created successfully',
                201
            );
        });
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
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function update(UpdateAnimalRequest $request, string $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            $animalData = $this->animalService->updateAnimal($request, $id);

            // Clear cache
            $userId = Auth::id();
            Cache::forget("animal_{$id}_user_{$userId}");

            return $this->successResponse($animalData, 'Animal updated successfully');
        });
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
     *             @OA\Property(property="message", type="string", example="Failed to delete animal"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $this->animalService->deleteAnimal($id);

            // Clear cache
            $userId = Auth::id();
            Cache::forget("animal_{$id}_user_{$userId}");

            return $this->successResponse(null, 'Animal deleted successfully');
        });
    }

    /**
     * Validate index request parameters
     */
    private function validateIndexRequest(Request $request): void
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,sold,deceased',
            'breed' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:male,female',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1'
        ]);
    }

    /**
     * Generate cache key for index request
     */
    private function generateCacheKey(Request $request, int $userId): string
    {
        $params = [
            'user' => $userId,
            'type' => $request->input('type', 'all'),
            'status' => $request->input('status', 'all'),
            'breed' => $request->input('breed', 'all'),
            'gender' => $request->input('gender', 'all'),
            'search' => $request->input('search', ''),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', self::DEFAULT_PER_PAGE)
        ];

        return 'animals_' . md5(serialize($params));
    }
}
