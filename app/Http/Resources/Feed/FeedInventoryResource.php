<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedInventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Inventory_id' => $this->id,
            //'user_id' => $this->user_id,
           // 'animal_id' => $this->animal_id,
            'feed_type_id' => $this->feed_type_id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'currency' => $this->currency,
            'purchase_date' => $this->purchase_date,
            'expiry_date' => $this->expiry_date,
            'batch_number' => $this->batch_number,
            'supplier' => $this->supplier,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
