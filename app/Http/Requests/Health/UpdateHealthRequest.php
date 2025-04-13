<?php

namespace App\Http\Requests\Health;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateHealthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    public function rules(): array
    {
        return [
            'health_status' => 'sometimes|string|max:255',
            'vaccination_status' => 'sometimes|string|max:255',
            'vet_contact_id' => 'sometimes|nullable|uuid',
            'vet_contact_uuid' => 'sometimes|nullable|uuid',
            'medical_history' => 'sometimes|array',
            'medical_history.*' => 'nullable|string',
            'dietary_restrictions' => 'sometimes|array',
            'dietary_restrictions.*' => 'nullable|string',
            'neutered_spayed' => 'sometimes|boolean',
            'regular_medication' => 'sometimes|array',
            'regular_medication.*' => 'nullable|string',
            'last_vet_visit' => 'sometimes|date',
            'insurance_details' => 'sometimes|string|max:255',
            'exercise_requirements' => 'sometimes|array',
            'exercise_requirements.*' => 'nullable|string',
            'parasite_prevention' => 'sometimes|array',
            'parasite_prevention.*' => 'nullable|string',
            'vaccinations' => 'sometimes|array',
            'vaccinations.*' => 'nullable|string',
            'allergies' => 'sometimes|array',
            'allergies.*' => 'nullable|string',
            'notes' => 'sometimes|array',
            'notes.*' => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Handle UUID fields
        if ($this->has('vet_contact_id')) {
            // If empty or not a valid UUID, set to null
            if (empty($this->input('vet_contact_id')) || !Str::isUuid($this->input('vet_contact_id'))) {
                $this->merge(['vet_contact_id' => null]);
            }
        }

        if ($this->has('vet_contact_uuid')) {
            // If empty or not a valid UUID, set to null
            if (empty($this->input('vet_contact_uuid')) || !Str::isUuid($this->input('vet_contact_uuid'))) {
                $this->merge(['vet_contact_uuid' => null]);
            }
        }

        // Convert string inputs to arrays if needed
        $arrayFields = [
            'medical_history',
            'dietary_restrictions',
            'regular_medication',
            'exercise_requirements',
            'parasite_prevention',
            'vaccinations',
            'allergies',
            'notes',
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && !is_array($this->input($field))) {
                if (empty($this->input($field))) {
                    $this->merge([$field => []]);
                } else {
                    // Convert non-empty string to single-item array
                    $this->merge([$field => [$this->input($field)]]);
                }
            }
        }
    }
}
