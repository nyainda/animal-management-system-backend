<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductGradeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'grade_name' => $this->grade_name,
            'description' => $this->description,
            'price_modifier' => $this->price_modifier,
        ];
    }
}
