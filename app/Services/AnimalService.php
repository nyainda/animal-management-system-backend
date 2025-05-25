<?php

namespace App\Services;

use App\Models\Animal;
use App\Enums\AnimalStatus;
use App\Http\Requests\Animal\{StoreAnimalRequest, UpdateAnimalRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

class AnimalService
{
    private const DEFAULT_PER_PAGE = 15;
    private const ANIMAL_SELECT_FIELDS = [
        'id', 'name', 'type', 'breed', 'status',
        'tag_number', 'birth_date', 'gender',
        'weight', 'height', 'is_breeding_stock', 'internal_id'
    ];

    private const ANIMAL_RELATIONS = [
        'birthDetail:id,animal_id,birth_weight,birth_status,health_at_birth',
        'damRelationship:id,animal_id,related_animal_id',
        'sireRelationship:id,animal_id,related_animal_id',
        'damRelationship.relatedAnimal:id,name,type,gender,birth_date',
        'sireRelationship.relatedAnimal:id,name,type,gender,birth_date',
    ];

    /**
     * Get paginated animals with filters
     */
    public function getPaginatedAnimals(Request $request, int|string $userId): LengthAwarePaginator
    {
        // Ensure userId is an integer
        $userId = is_string($userId) ? (int) $userId : $userId;

        $query = $this->buildBaseQuery()->forUser($userId);
        $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', self::DEFAULT_PER_PAGE), 100);

        return $query->paginate($perPage);
    }

    /**
     * Get animal with full details
     */
    public function getAnimalWithDetails(string $id, int|string $userId): array
    {
        // Ensure userId is an integer
        $userId = is_string($userId) ? (int) $userId : $userId;

        $animal = $this->buildBaseQuery()
            ->forUser($userId)
            ->findOrFail($id);

        return $this->formatAnimalResponse($animal);
    }

    /**
     * Create a new animal
     */
    public function createAnimal(StoreAnimalRequest $request): array
    {
        $validatedData = $this->prepareAnimalData($request->validated());
        $animal = Animal::create($validatedData);

        $this->handleBirthDetails($animal, $request->validatedBirthDetails());
        $animal->load('birthDetail');

        return $this->mergeAnimalWithBirthDetails($animal);
    }

    /**
     * Update an existing animal
     */
    public function updateAnimal(UpdateAnimalRequest $request, string $id): array
    {
        $animal = Animal::forUser(Auth::id())->findOrFail($id);
        $validatedData = $this->prepareAnimalData($request->validated());

        $this->logStatusChange($animal, $validatedData);
        $animal->update($validatedData);

        $this->handleBirthDetails($animal, $request->validatedBirthDetails());
        $animal->refresh();

        return $this->formatAnimalResponse($animal);
    }

    /**
     * Delete an animal
     */
    public function deleteAnimal(string $id): void
    {
        $animal = Animal::forUser(Auth::id())->findOrFail($id);

        // Delete birth details first due to foreign key constraint
        optional($animal->birthDetail)->delete();
        $animal->delete();
    }

    /**
     * Build base query with optimized select and relationships
     */
    private function buildBaseQuery(): Builder
    {
        return Animal::query()
            ->select(self::ANIMAL_SELECT_FIELDS)
            ->with(self::ANIMAL_RELATIONS);
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Basic filters
        $basicFilters = ['type', 'status', 'breed', 'gender'];
        foreach ($basicFilters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('tag_number', 'LIKE', "%{$search}%")
                  ->orWhere('internal_id', 'LIKE', "%{$search}%");
            });
        }

        // Date filters
        if ($request->filled('birth_date_from')) {
            $query->where('birth_date', '>=', $request->input('birth_date_from'));
        }

        if ($request->filled('birth_date_to')) {
            $query->where('birth_date', '<=', $request->input('birth_date_to'));
        }

        // Breeding stock filter
        if ($request->filled('is_breeding_stock')) {
            $query->where('is_breeding_stock', $request->boolean('is_breeding_stock'));
        }

        // Weight range filters
        if ($request->filled('min_weight')) {
            $query->where('weight', '>=', $request->input('min_weight'));
        }

        if ($request->filled('max_weight')) {
            $query->where('weight', '<=', $request->input('max_weight'));
        }

        // Sort by
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortFields = [
            'name', 'type', 'breed', 'birth_date', 'gender',
            'weight', 'height', 'status', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');
        }
    }

    /**
     * Prepare animal data for storage
     */
    private function prepareAnimalData(array $data): array
    {
        $data['user_id'] = Auth::id();

        // Handle status enum conversion
        if (isset($data['status'])) {
            $status = AnimalStatus::tryFromCaseInsensitive($data['status']);
            $data['status'] = $status ? $status->value : AnimalStatus::ACTIVE->value;
        }

        // Clean and validate numeric fields
        if (isset($data['weight'])) {
            $data['weight'] = max(0, (float) $data['weight']);
        }

        if (isset($data['height'])) {
            $data['height'] = max(0, (float) $data['height']);
        }

        // Normalize string fields
        $stringFields = ['name', 'type', 'breed', 'tag_number', 'internal_id'];
        foreach ($stringFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Handle birth details creation/update
     */
    private function handleBirthDetails(Animal $animal, ?array $birthDetails): void
    {
        if (!$birthDetails) {
            return;
        }

        // Clean birth details data
        $cleanBirthDetails = array_filter($birthDetails, function ($value) {
            return !is_null($value) && $value !== '';
        });

        if (!empty($cleanBirthDetails)) {
            $animal->birthDetail()->updateOrCreate(
                ['animal_id' => $animal->id],
                $cleanBirthDetails
            );
        }
    }

    /**
     * Format animal response with all details
     */
    private function formatAnimalResponse(Animal $animal): array
    {
        $data = $this->mergeAnimalWithDetails($animal);

        // Add status information
        $data['status_group'] = $animal->status->getGroup();
        $data['status_color'] = $animal->status->getStatusColor();
        $data['status_action'] = $animal->status->requiresAction();

        // Add computed fields
        $data['age_in_days'] = $animal->birth_date ?
            now()->diffInDays($animal->birth_date) : null;

        $data['is_adult'] = $animal->birth_date ?
            now()->diffInYears($animal->birth_date) >= 2 : false;

        return $data;
    }

    /**
     * Merge animal with birth details
     */
    private function mergeAnimalWithBirthDetails(Animal $animal): array
    {
        $animalData = $animal->toArray();
        $birthDetails = Arr::pull($animalData, 'birth_detail', []);

        return array_merge($animalData, $birthDetails);
    }

    /**
     * Merge animal with all relationship details
     */
    private function mergeAnimalWithDetails(Animal $animal): array
    {
        $animalData = $this->mergeAnimalWithBirthDetails($animal);

        // Add parent information
        $animalData['dam'] = $this->formatParentData($animal->dam());
        $animalData['sire'] = $this->formatParentData($animal->sire());

        // Add offspring information
        $animalData['offspring'] = $animal->offspring()->map(function ($offspring) {
            return $offspring->only(['id', 'name', 'type', 'gender', 'birth_date']);
        })->toArray();

        // Add offspring count
        $animalData['offspring_count'] = count($animalData['offspring']);

        return $animalData;
    }

    /**
     * Format parent data safely
     */
    private function formatParentData($parent): ?array
    {
        if (!$parent) {
            return null;
        }

        return $parent->only(['id', 'name', 'type', 'gender', 'birth_date']);
    }

    /**
     * Log status changes for audit trail
     */
    private function logStatusChange(Animal $animal, array $data): void
    {
        if (!isset($data['status']) || $animal->status->value === $data['status']) {
            return;
        }

        Log::info('Animal Status Change', [
            'animal_id' => $animal->id,
            'animal_name' => $animal->name,
            'from_status' => $animal->status->value,
            'to_status' => $data['status'],
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get animals summary statistics
     */
    public function getAnimalsSummary(int|string $userId): array
    {
        // Ensure userId is an integer
        $userId = is_string($userId) ? (int) $userId : $userId;

        $baseQuery = Animal::forUser($userId);

        return [
            'total_animals' => $baseQuery->count(),
            'active_animals' => $baseQuery->where('status', AnimalStatus::ACTIVE->value)->count(),
            'breeding_stock' => $baseQuery->where('is_breeding_stock', true)->count(),
            'by_gender' => [
                'male' => $baseQuery->where('gender', 'male')->count(),
                'female' => $baseQuery->where('gender', 'female')->count(),
            ],
            'by_status' => $baseQuery->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'average_weight' => $baseQuery->whereNotNull('weight')->avg('weight'),
            'recent_births' => $baseQuery->where('birth_date', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Get breeding candidates
     */
    public function getBreedingCandidates(int|string $userId, ?string $gender = null): Collection
    {
        // Ensure userId is an integer
        $userId = is_string($userId) ? (int) $userId : $userId;

        $query = Animal::forUser($userId)
            ->where('is_breeding_stock', true)
            ->where('status', AnimalStatus::ACTIVE->value);

        if ($gender) {
            $query->where('gender', $gender);
        }

        return $query->select(['id', 'name', 'type', 'breed', 'gender', 'birth_date'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get animals by type with counts
     */
    public function getAnimalsByType(int|string $userId): array
    {
        // Ensure userId is an integer
        $userId = is_string($userId) ? (int) $userId : $userId;

        return Animal::forUser($userId)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'type')
            ->toArray();
    }
}
