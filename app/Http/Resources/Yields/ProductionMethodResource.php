<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductionMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'method_name' => $this->method_name,
            'description' => $this->description,
            'requires_certification' => $this->requires_certification,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
