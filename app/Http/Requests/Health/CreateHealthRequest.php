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
            'medical_history' => 'nullable|array',
            'dietary_restrictions' => 'nullable|array',
            'neutered_spayed' => 'nullable|boolean',
            'regular_medication' => 'nullable|array',
            'last_vet_visit' => 'nullable|date',
            'insurance_details' => 'nullable|string|max:255',
            'exercise_requirements' => 'nullable|array',
            'parasite_prevention' => 'nullable|array',
            'vaccinations' => 'nullable|array',
            'allergies' => 'nullable|array',
            'notes' => 'nullable|array',
        ];
    }
}
