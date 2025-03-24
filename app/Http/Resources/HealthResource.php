<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="HealthResource",
 *     type="object",
 *     title="Health Resource",
 *     description="A resource representing an animal's health record",
 *     @OA\Property(property="Health_id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8", description="Unique identifier of the health record"),
 *     @OA\Property(property="animal_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="UUID of the associated animal"),
 *     @OA\Property(property="health_status", type="string", example="Healthy", description="Current health status of the animal"),
 *     @OA\Property(property="vaccination_status", type="string", example="Up-to-date", description="Vaccination status of the animal"),
 *     @OA\Property(property="vet_contact_id", type="string", format="uuid", nullable=true, example="123e4567-e89b-12d3-a456-426614174000", description="UUID of the vet contact"),
 *     @OA\Property(property="medical_history", type="string", nullable=true, example="Recovered from flu in 2024", description="Medical history of the animal"),
 *     @OA\Property(property="dietary_restrictions", type="string", nullable=true, example="No dairy", description="Dietary restrictions"),
 *     @OA\Property(property="neutered_spayed", type="boolean", example=true, description="Whether the animal is neutered or spayed"),
 *     @OA\Property(property="regular_medication", type="string", nullable=true, example="Daily vitamin supplement", description="Regular medication details"),
 *     @OA\Property(property="last_vet_visit", type="string", format="date", nullable=true, example="2025-03-20", description="Date of the last vet visit"),
 *     @OA\Property(property="insurance_details", type="string", nullable=true, example="Policy #12345", description="Insurance details"),
 *     @OA\Property(property="exercise_requirements", type="string", nullable=true, example="30 min walk daily", description="Exercise requirements"),
 *     @OA\Property(property="parasite_prevention", type="string", nullable=true, example="Monthly flea treatment", description="Parasite prevention measures"),
 *     @OA\Property(property="vaccinations", type="string", nullable=true, example="Rabies: 2025-01-01", description="Vaccination details"),
 *     @OA\Property(property="allergies", type="string", nullable=true, example="Peanuts", description="Known allergies"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Monitor weight", description="Additional notes"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Timestamp when the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="Timestamp when the record was last updated")
 * )
 */
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