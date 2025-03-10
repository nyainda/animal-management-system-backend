<?php

namespace App\Http\Requests\Animal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\AnimalStatus;
use Illuminate\Validation\Rules\Enum;
class UpdateAnimalRequest extends FormRequest
{
    private const ANIMAL_FIELDS = [
        'name', 'type', 'breed', 'tag_number', 'birth_date',
        'weight', 'height', 'coloring', 'status', 'keywords',
        'physical_traits', 'is_breeding_stock'
    ];

    private const BIRTH_DETAIL_FIELDS = [
        'birth_time', 'birth_status', 'colostrum_intake',
        'health_at_birth', 'birth_weight', 'multiple_birth',
        'birth_order', 'gestation_length', 'vaccinations',
        'milk_feeding', 'breeder_info', 'raised_purchased'
    ];

    public function authorize(): bool
    {
        return true; // Consider adding authorization logic if needed
    }

    public function rules(): array
    {
        $animalId = $this->route('animal'); // Retrieve the animal ID from the route

        return array_merge(
            $this->animalRules($animalId),
            $this->birthDetailRules()
        );
    }

    protected function animalRules(string $animalId): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'breed' => 'sometimes|string',
            'tag_number' => [
            'sometimes',
            'string',
            Rule::unique('animals')->ignore($animalId)
            ],
            'birth_date' => 'nullable|date',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'coloring' => 'nullable|string',
            'status' => [
                'sometimes',
                'string',
                //new Enum(AnimalStatus::class)
                function ($attribute, $value, $fail) {
                    if (!AnimalStatus::tryFromCaseInsensitive($value)) {
                        $fail("Invalid status value: {$value}");
                    }
                }
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
            'health_at_birth' => 'sometimes|string',
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
        return Arr::only(
            parent::validated(),
            self::ANIMAL_FIELDS
        );
    }

    public function validatedBirthDetails(): array
    {
        return Arr::only(
            parent::validated(),
            self::BIRTH_DETAIL_FIELDS
        );
    }
}
