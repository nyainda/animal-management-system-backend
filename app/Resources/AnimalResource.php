<?php

// AnimalResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'gender' => $this->gender,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'birth_detail' => BirthDetailResource::make($this->whenLoaded('birthDetail')),
            'dam' => self::make($this->whenLoaded('damRelationship.relatedAnimal')),
            'sire' => self::make($this->whenLoaded('sireRelationship.relatedAnimal')),
            'offspring' => self::collection($this->whenLoaded('offspringRelationships.animal'))
        ];
    }
}

