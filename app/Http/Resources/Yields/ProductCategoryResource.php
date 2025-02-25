<?php


// app/Http/Resources/ProductCategoryResource.php
namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'measurement_unit' => $this->measurement_unit,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'grades' => ProductGradeResource::collection($this->whenLoaded('grades')),
        ];
    }
}

