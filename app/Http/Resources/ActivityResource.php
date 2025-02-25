<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'animal_id' => $this->animal_id,
            'activity_type' => $this->activity_type,
            'activity_date' => $this->activity_date,
            'description' => $this->description,
            'notes' => $this->notes,
            'breeding_date' => $this->breeding_date,
            'breeding_notes' => $this->breeding_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
