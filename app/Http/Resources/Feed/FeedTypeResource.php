<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedTypeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'recommended_storage' => $this->recommended_storage,
            'shelf_life_days' => $this->shelf_life_days,
            'nutritional_info' => $this->nutritional_info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
