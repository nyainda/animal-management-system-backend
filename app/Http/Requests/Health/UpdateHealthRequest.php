<?php

namespace App\Http\Requests\Health;

use Illuminate\Foundation\Http\FormRequest;

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
            'vet_contact_id' => 'sometimes|uuid',
            'medical_history' => 'sometimes|array',
            'dietary_restrictions' => 'sometimes|array',
            'neutered_spayed' => 'sometimes|boolean',
            'regular_medication' => 'sometimes|array',
            'last_vet_visit' => 'sometimes|date',
            'insurance_details' => 'sometimes|string|max:255',
            'exercise_requirements' => 'sometimes|array',
            'parasite_prevention' => 'sometimes|array',
            'vaccinations' => 'sometimes|array',
            'allergies' => 'sometimes|array',
            'notes' => 'sometimes|array',
        ];
    }
}
