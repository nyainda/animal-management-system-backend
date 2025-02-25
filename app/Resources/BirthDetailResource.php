<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BirthDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'birth_date' => $this->birth_date->toIso8601String(),
            'birth_weight' => $this->birth_weight,
            'breeder_id' => $this->breeder_id,
            'birth_location' => $this->birth_location,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Optional relationship fields
            'animal' => $this->whenLoaded('animal', function() {
                return [
                    'id' => $this->animal->id,
                    'name' => $this->animal->name
                ];
            }),

            // Computed fields example
            'formatted_weight' => $this->formatted_weight,
        ];
    }
}
