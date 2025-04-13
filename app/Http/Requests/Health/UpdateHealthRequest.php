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
            'health_status' => 'sometimes|nullable|string|max:255',
            'vaccination_status' => 'sometimes|nullable|string|max:255',
            'vet_contact_id' => 'sometimes|nullable|uuid',
            'vet_contact_uuid' => 'sometimes|nullable|uuid',
            'medical_history' => 'sometimes|array',
            'medical_history.*' => 'nullable|string',
            'dietary_restrictions' => 'sometimes|array',
            'dietary_restrictions.*' => 'nullable|string',
            'neutered_spayed' => 'sometimes|nullable|boolean',
            'regular_medication' => 'sometimes|array',
            'regular_medication.*' => 'nullable|string',
            'last_vet_visit' => 'sometimes|nullable|date',
            'insurance_details' => 'sometimes|nullable|string|max:255',
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
            if (empty($this->input('vet_contact_id')) || !Str::isUuid($this->input('vet_contact_id'))) {
                $this->merge(['vet_contact_id' => null]);
            }
        }

        if ($this->has('vet_contact_uuid')) {
            if (empty($this->input('vet_contact_uuid')) || !Str::isUuid($this->input('vet_contact_uuid'))) {
                $this->merge(['vet_contact_uuid' => null]);
            }
        }

        // Handle insurance_details field
        if ($this->has('insurance_details') && !is_string($this->input('insurance_details'))) {
            if (is_array($this->input('insurance_details'))) {
                // Convert array to string if possible
                $this->merge(['insurance_details' => implode(', ', array_filter($this->input('insurance_details')))]);
            } else {
                // Convert to string or set to null if empty
                $this->merge(['insurance_details' => empty($this->input('insurance_details')) ? null : (string)$this->input('insurance_details')]);
            }
        }

        // Handle last_vet_visit field
        if ($this->has('last_vet_visit')) {
            $date = $this->input('last_vet_visit');
            if (empty($date)) {
                $this->merge(['last_vet_visit' => null]);
            } elseif (is_array($date)) {
                // If it's an array (like from a form with year, month, day inputs)
                $this->merge(['last_vet_visit' => null]);
            } else {
                try {
                    // Try to parse the date or set to null if it's invalid
                    $parsedDate = date('Y-m-d', strtotime($date));
                    if ($parsedDate === '1970-01-01' && $date !== '1970-01-01') {
                        $this->merge(['last_vet_visit' => null]);
                    }
                } catch (\Exception $e) {
                    $this->merge(['last_vet_visit' => null]);
                }
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
