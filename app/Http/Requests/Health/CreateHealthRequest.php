<?php

namespace App\Http\Requests\Health;

use Illuminate\Foundation\Http\FormRequest;

class CreateHealthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    public function rules(): array
    {
        return [
            'health_status' => 'nullable|string|max:255',
            'vaccination_status' => 'nullable|string|max:255',
            'vet_contact_id' => 'nullable|uuid',
            'vet_contact_uuid' => 'nullable|uuid', // Added optional UUID field
            'medical_history' => 'nullable|array',
            'medical_history.*' => 'nullable|string', // Validate array items
            'dietary_restrictions' => 'nullable|array',
            'dietary_restrictions.*' => 'nullable|string',
            'neutered_spayed' => 'nullable|boolean',
            'regular_medication' => 'nullable|array',
            'regular_medication.*' => 'nullable|string',
            'last_vet_visit' => 'nullable|date',
            'insurance_details' => 'nullable|string|max:255',
            'exercise_requirements' => 'nullable|array',
            'exercise_requirements.*' => 'nullable|string',
            'parasite_prevention' => 'nullable|array',
            'parasite_prevention.*' => 'nullable|string',
            'vaccinations' => 'nullable|array',
            'vaccinations.*' => 'nullable|string',
            'allergies' => 'nullable|array',
            'allergies.*' => 'nullable|string',
            'notes' => 'nullable|array',
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
