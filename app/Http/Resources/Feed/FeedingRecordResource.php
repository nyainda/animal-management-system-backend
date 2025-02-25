<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedingRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Record_id' => $this->id,
           // 'user_id' => $this->user_id,
            //'animal_id' => $this->animal_id,
            'feed_type_id' => $this->feed_type_id,
            'feed_inventory_id' => $this->feed_inventory_id,
           // 'schedule_id' => $this->schedule_id,
            'amount' => $this->amount,
            'unit' => $this->unit,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'fed_at' => $this->fed_at,
            'notes' => $this->notes,
            'feeding_method' => $this->feeding_method,
            'consumption_notes' => $this->consumption_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
