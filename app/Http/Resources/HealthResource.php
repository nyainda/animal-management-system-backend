<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Health_id' => $this->id,
            'animal_id' => $this->animal_id,
            'health_status' => $this->health_status,
            'vaccination_status' => $this->vaccination_status,
            'vet_contact_id' => $this->vet_contact_id,
            'medical_history' => $this->medical_history,
            'dietary_restrictions' => $this->dietary_restrictions,
            'neutered_spayed' => $this->neutered_spayed,
            'regular_medication' => $this->regular_medication,
            'last_vet_visit' => $this->last_vet_visit,
            'insurance_details' => $this->insurance_details,
            'exercise_requirements' => $this->exercise_requirements,
            'parasite_prevention' => $this->parasite_prevention,
            'vaccinations' => $this->vaccinations,
            'allergies' => $this->allergies,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
