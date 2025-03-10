<?php

namespace App\Http\Requests\Animal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\Animal;
use App\Enums\AnimalStatus;

use Illuminate\Validation\Rules\Enum;
class StoreAnimalRequest extends FormRequest
{
    private const TAG_PREFIX = 'TAG';
    private const INTERNAL_ID_FORMAT = '%s/%s/%04d';
    private const DEFAULT_STATUS = 'active';
    private const DEFAULT_RAISED = 'raised';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->animalRules(),
            $this->birthDetailRules()
        );
    }

    protected function animalRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'breed' => 'required|string',
            'gender' => 'required|in:male,female',
            'tag_number' => 'sometimes|string',
            'birth_date' => 'nullable|date',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'coloring' => 'nullable|string',
            'status' => [
                'sometimes',
                new Enum(AnimalStatus::class)
            ],
            'keywords' => 'sometimes|array',
            'physical_traits' => 'sometimes|array',
            'is_breeding_stock' => 'sometimes|boolean',


        ];
    }

    protected function birthDetailRules(): array
    {
        return [
            'birth_time' => 'nullable|date',
            'birth_status' => 'nullable|string',
            'colostrum_intake' => 'nullable|integer',
            'health_at_birth' => 'required|string',
            'birth_weight' => 'nullable|numeric',
            'multiple_birth' => 'nullable|boolean',
            'birth_order' => 'nullable|integer',
            'gestation_length' => 'nullable|integer',
            'vaccinations' => 'nullable|array',
            'milk_feeding' => 'nullable|string',
            'breeder_info' => 'nullable|string',
            'raised_purchased' => 'nullable|string'
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return $this->processAnimalData($validated);
    }

    protected function processAnimalData(array $data): array
    {
        $data['tag_number'] ??= $this->generateUniqueTagNumber($data['type']);

        return $this->mergeDefaults($data, [
            'status' => AnimalStatus::ACTIVE->value,
            'keywords' => [],
            'physical_traits' => [],
            'is_breeding_stock' => false,
            'id' => (string) Str::uuid(),
            'internal_id' => $this->generateInternalId($data['type'])
        ], [
            'name', 'type', 'breed', 'gender', 'tag_number',
            'birth_date', 'weight', 'height', 'coloring',
            'status', 'keywords', 'physical_traits',
            'is_breeding_stock', 'id', 'internal_id'
        ]);
    }

    public function validatedBirthDetails(): array
    {
        $validated = parent::validated();

        return $this->mergeDefaults($validated, [
            'multiple_birth' => false,
            'raised_purchased' => self::DEFAULT_RAISED,
            'vaccinations' => []
        ], [
            'birth_time', 'birth_status', 'colostrum_intake',
            'health_at_birth', 'birth_weight', 'multiple_birth',
            'birth_order', 'gestation_length', 'vaccinations',
            'milk_feeding', 'breeder_info', 'raised_purchased'
        ]);
    }

    protected function mergeDefaults(array $data, array $defaults, array $fields): array
    {
        return array_merge(
            $defaults,
            Arr::only($data, $fields)
        );
    }

    private function generateUniqueTagNumber(string $type): string
    {
        do {
            $tagNumber = $this->generateTagNumber();
        } while (Animal::where('tag_number', $tagNumber)->exists());

        return $tagNumber;
    }

    private function generateTagNumber(): string
    {
        return self::TAG_PREFIX . date('y') . str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function generateInternalId(string $type): string
    {
        $count = Animal::whereYear('created_at', now())
            ->where('type', $type)
            ->count() + 1;

        return sprintf(
            self::INTERNAL_ID_FORMAT,
            strtoupper($type),
            now()->format('y'),
            $count
        );
    }
}
