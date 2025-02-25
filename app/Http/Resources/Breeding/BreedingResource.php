<?php

// app/Http/Resources/BreedingResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BreedingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            //'animal_id' => $this->animal_id,
           // 'mate_id' => $this->mate_id,
           // 'user_id' => $this->user_id,
            'breeding_status' => $this->breeding_status->value,
            'heat_date' => $this->heat_date?->format('Y-m-d'),
            'breeding_date' => $this->breeding_date->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'pregnancy_status' => $this->pregnancy_status?->value,
            'offspring_count' => $this->offspring_count,
            'offspring_details' => $this->offspring_details,
            'remarks' => $this->remarks,
            'health_notes' => $this->health_notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

